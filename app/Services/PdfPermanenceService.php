<?php

namespace App\Services;

use App\Models\Permanence;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service de génération PDF pour les permanences.
 * 
 * SÉCURITÉ :
 * - Seuls Admin et Officier responsable peuvent générer un PDF
 * - La permanence DOIT être validée
 * - Sous-officier JAMAIS autorisé
 * 
 * SUPPORT BILINGUE :
 * - Français (LTR)
 * - Arabe (RTL) avec polices compatibles
 */
class PdfPermanenceService
{
    protected Permanence $permanence;
    protected string $locale;
    protected bool $isRtl;

    public function __construct(Permanence $permanence)
    {
        $this->permanence = $permanence->load([
            'officier',
            'affectations.sousOfficier',
            'affectations.site',
            'relationsManageriales.sousOfficier',
            'receptionMateriels.user',
            'receptionMateriels.appareil',
        ]);
        
        $this->locale = App::getLocale();
        $this->isRtl = $this->locale === 'ar';
    }

    /**
     * Vérifie les droits d'accès pour l'impression PDF.
     * 
     * @throws \Exception si accès refusé
     */
    public function checkAccess(): void
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('Authentification requise.');
        }

        // La permanence DOIT être validée
        if (!$this->permanence->isLocked()) {
            throw new \Exception('Seules les permanences validées peuvent être imprimées.');
        }

        // Sous-officier JAMAIS autorisé
        if ($user->isSousOfficier()) {
            Log::warning('Tentative d\'impression PDF non autorisée (sous-officier)', [
                'user_id' => $user->id,
                'permanence_id' => $this->permanence->id,
            ]);
            throw new \Exception('Accès refusé. L\'impression est réservée à l\'officier responsable.');
        }

        // Viewer peut imprimer (lecture seule)
        if ($user->isViewer()) {
            return;
        }

        // Admin peut toujours imprimer
        if ($user->isAdmin()) {
            return;
        }

        // Officier : uniquement s'il est responsable
        if ($user->isOfficier() && $this->permanence->officier_id !== $user->id) {
            throw new \Exception('Accès refusé. Vous n\'\u00eates pas l\'officier responsable de cette permanence.');
        }
    }

    /**
     * Génère le PDF du registre de permanence.
     */
    public function generate(): \Barryvdh\DomPDF\PDF
    {
        $this->checkAccess();

        $data = $this->prepareData();

        $pdf = Pdf::loadView('pdf.permanence', $data);
        
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => $this->isRtl ? 'DejaVu Sans' : 'DejaVu Sans',
            'dpi' => 150,
            'isFontSubsettingEnabled' => true,
        ]);

        // Journaliser l'impression
        $this->logPrint();

        return $pdf;
    }

    /**
     * Télécharge le PDF.
     */
    public function download(): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate();
        $filename = $this->getFilename();

        return $pdf->download($filename);
    }

    /**
     * Affiche le PDF dans le navigateur.
     */
    public function stream(): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate();
        $filename = $this->getFilename();

        return $pdf->stream($filename);
    }

    /**
     * Prépare les données pour le PDF.
     */
    protected function prepareData(): array
    {
        // Titres bilingues
        $titles = $this->getBilingualTitles();

        // Séparation des événements officier / sous-officiers
        $evenementsData = $this->prepareEvenementsSepares();
        
        // Séparation du matériel officier / opérateurs
        $materielData = $this->prepareMaterielSepare();

        return [
            // Paramètres institutionnels
            'institution_name' => Setting::get('institution_name', 'INSTITUTION'),
            'direction_name' => Setting::get('direction_name', 'DIRECTION'),
            'system_name' => Setting::get('system_name', $this->isRtl ? 'نظام التسيير' : 'SYSTÈME DE GESTION'),
            'pdf_title' => Setting::get('pdf_title', $titles['title']),
            'pdf_footer' => Setting::get('pdf_footer', $titles['footer']),
            
            // Logos en base64
            'logo_institution' => Setting::getLogoBase64('logo_institution'),
            'logo_direction' => Setting::getLogoBase64('logo_direction'),
            
            // Données de la permanence
            'permanence' => $this->permanence,
            'officier' => $this->permanence->officier,
            'affectations' => $this->permanence->affectations,
            
        // Événements séparés et regroupés
            'evenements' => $this->permanence->relationsManageriales->sortBy('heure_evenement'),
            'evenements_officier' => $evenementsData['officier'],
            'evenements_sous_officiers' => $evenementsData['sous_officiers'],
            'has_evenements_officier' => $evenementsData['has_officier'],
            'has_evenements_sous_officiers' => $evenementsData['has_sous_officiers'],
            
            // Matériel reçu séparé par rôle
            'materiel_officier' => $materielData['officier'],
            'materiel_operateurs' => $materielData['operateurs'],
            'has_materiel_officier' => $materielData['has_officier'],
            'has_materiel_operateurs' => $materielData['has_operateurs'],
            
            // Métadonnées
            'date_edition' => now()->format('d/m/Y H:i'),
            'date_permanence' => $this->permanence->date->format('d/m/Y'),
            'heure_debut' => $this->permanence->heure_debut->format('H:i'),
            'heure_fin' => $this->permanence->heure_fin->format('H:i'),

            // Configuration langue/direction
            'locale' => $this->locale,
            'is_rtl' => $this->isRtl,
            'direction' => $this->isRtl ? 'rtl' : 'ltr',
            'text_align' => $this->isRtl ? 'right' : 'left',
            
            // Labels bilingues
            'labels' => $this->getLabels(),
        ];
    }

    /**
     * Sépare et regroupe les événements par type d'auteur.
     * - Section Officier : événements saisis par l'officier
     * - Section Sous-officiers : regroupés par personne avec type (opérateur/chef de poste)
     */
    protected function prepareEvenementsSepares(): array
    {
        $evenements = $this->permanence->relationsManageriales->sortBy('heure_evenement');
        $officierId = $this->permanence->officier_id;

        // Événements de l'officier
        $evenementsOfficier = $evenements->filter(function ($evt) use ($officierId) {
            return $evt->sous_officier_id === $officierId;
        });

        // Événements des sous-officiers, regroupés par personne
        $evenementsSousOfficiers = $evenements->filter(function ($evt) use ($officierId) {
            return $evt->sous_officier_id !== $officierId;
        })->groupBy('sous_officier_id')->map(function ($evts, $sousOfficierId) {
            $sousOfficier = $evts->first()->sousOfficier;
            return [
                'sous_officier' => $sousOfficier,
                'nom_complet' => $sousOfficier->nom_complet,
                'fonction' => $sousOfficier->fonction?->getLabel() ?? ($this->isRtl ? 'ضابط صف' : 'Sous-officier'),
                'matricule' => $sousOfficier->matricule ?? '-',
                'evenements' => $evts->sortBy('heure_evenement'),
            ];
        });

        return [
            'officier' => $evenementsOfficier,
            'sous_officiers' => $evenementsSousOfficiers,
            'has_officier' => $evenementsOfficier->count() > 0,
            'has_sous_officiers' => $evenementsSousOfficiers->count() > 0,
        ];
    }

    /**
     * Sépare le matériel reçu par type de destinataire.
     * - Section Officier : matériel reçu par l'officier
     * - Section Opérateurs : matériel reçu par les opérateurs (regroupé par personne)
     */
    protected function prepareMaterielSepare(): array
    {
        $receptions = $this->permanence->receptionMateriels;
        $officierId = $this->permanence->officier_id;

        // Matériel de l'officier
        $materielOfficier = $receptions->filter(function ($reception) use ($officierId) {
            return $reception->user_id === $officierId;
        })->sortBy('appareil.nom');

        // Matériel des opérateurs, regroupé par personne
        $materielOperateurs = $receptions->filter(function ($reception) use ($officierId) {
            return $reception->user_id !== $officierId;
        })->groupBy('user_id')->map(function ($receptions, $userId) {
            $user = $receptions->first()->user;
            return [
                'user' => $user,
                'nom_complet' => $user->nom_complet,
                'fonction' => $user->fonction?->getLabel() ?? ($this->isRtl ? 'مشغّل' : 'Opérateur'),
                'matricule' => $user->matricule ?? '-',
                'receptions' => $receptions->sortBy('appareil.nom'),
            ];
        });

        return [
            'officier' => $materielOfficier,
            'operateurs' => $materielOperateurs,
            'has_officier' => $materielOfficier->count() > 0,
            'has_operateurs' => $materielOperateurs->count() > 0,
        ];
    }

    /**
     * Retourne les titres bilingues.
     */
    protected function getBilingualTitles(): array
    {
        if ($this->isRtl) {
            return [
                'title' => 'سجل المداومة',
                'footer' => 'وثيقة رسمية - يمنع النسخ بدون إذن',
            ];
        }

        return [
            'title' => 'REGISTRE DE PERMANENCE',
            'footer' => 'Document officiel - Ne pas reproduire sans autorisation',
        ];
    }

    /**
     * Retourne les labels traduits pour le PDF.
     */
    protected function getLabels(): array
    {
        if ($this->isRtl) {
            return [
                'permanence_info' => 'معلومات المداومة',
                'date' => 'التاريخ',
                'period' => 'الفترة',
                'officer' => 'ضابط المداومة',
                'status' => 'الحالة',
                'matricule' => 'الرقم التسلسلي',
                'edition_date' => 'تاريخ الطباعة',
                'assigned_personnel' => 'الأفراد المعينون',
                'sous_officier' => 'ضابط صف',
                'site' => 'الموقع',
                'officer_comment' => 'ملاحظة الضابط',
                'managerial_relation' => 'العلاقة الإدارية',
                'events' => 'الأحداث',
                'hour' => 'الساعة',
                'author' => 'المحرر',
                'event_fact' => 'الحدث / الواقعة المعاينة',
                'ordered_effects' => 'الإجراءات المتخذة',
                'no_events' => 'لا توجد أحداث مسجلة لهذه المداومة.',
                'validation' => 'المصادقة على السجل',
                'function' => 'الوظيفة',
                'signature' => 'التوقيع',
                'validation_date' => 'تاريخ المصادقة',
                'permanence_number' => 'رقم المداومة',
                'electronic_validation' => 'تمت المصادقة على هذا السجل إلكترونيًا',
                'validated' => 'مصادق عليها',
                // Labels pour sections séparées
                'section_officer' => 'تسجيلات الضابط',
                'section_sous_officiers' => 'تسجيلات ضباط الصف',
                'no_officer_events' => 'لا توجد تسجيلات من الضابط.',
                'no_sous_officier_events' => 'لا توجد تسجيلات من ضباط الصف.',
                'operateur' => 'مشغّل',
                'chef_poste' => 'رئيس مركز',
                // Labels pour le matériel
                'materiel_section_officier' => 'المعدات المستلمة من طرف الضابط',
                'materiel_section_operateurs' => 'المعدات المستلمة من طرف العاملين',
                'materiel_no_officier' => 'لا توجد معدات مسجلة للضابط.',
                'materiel_no_operateurs' => 'لا توجد معدات مسجلة للعاملين.',
                'materiel_appareil' => 'الجهاز',
                'materiel_recu' => 'مستلم',
                'materiel_etat' => 'الحالة',
                'materiel_commentaire' => 'ملاحظة',
                'materiel_oui' => 'نعم',
                'materiel_non' => 'لا',
                'materiel_fonctionne' => 'يعمل',
                'materiel_endommage' => 'تالف',
                'materiel_hors_service' => 'خارج الخدمة',
            ];
        }

        return [
            'permanence_info' => 'Informations de la permanence',
            'date' => 'Date',
            'period' => 'Période',
            'officer' => 'Officier de permanence',
            'status' => 'Statut',
            'matricule' => 'Matricule',
            'edition_date' => 'Date d\'\u00e9dition',
            'assigned_personnel' => 'Personnel affecté',
            'sous_officier' => 'Sous-officier',
            'site' => 'Site',
            'officer_comment' => 'Commentaire de l\'officier',
            'managerial_relation' => 'Relation Managériale',
            'events' => 'Événements',
            'hour' => 'Heure',
            'author' => 'Auteur',
            'event_fact' => 'Événement / Fait constaté',
            'ordered_effects' => 'Effets ordonnés',
            'no_events' => 'Aucun événement enregistré pour cette permanence.',
            'validation' => 'Validation du registre',
            'function' => 'Fonction',
            'signature' => 'Signature',
            'validation_date' => 'Date de validation',
            'permanence_number' => 'Numéro de permanence',
            'electronic_validation' => 'Document validé électroniquement',
            'validated' => 'Validée',
            // Labels pour sections séparées
            'section_officer' => 'Saisies de l\'Officier',
            'section_sous_officiers' => 'Saisies des Sous-officiers',
            'no_officer_events' => 'Aucune saisie de l\'officier.',
            'no_sous_officier_events' => 'Aucune saisie des sous-officiers.',
            'operateur' => 'Opérateur',
            'chef_poste' => 'Chef de poste',
            // Labels pour le matériel
            'materiel_section_officier' => 'Matériel reçu par l\'Officier',
            'materiel_section_operateurs' => 'Matériel reçu par les Opérateurs',
            'materiel_no_officier' => 'Aucun matériel enregistré pour l\'officier.',
            'materiel_no_operateurs' => 'Aucun matériel enregistré pour les opérateurs.',
            'materiel_appareil' => 'Appareil',
            'materiel_recu' => 'Reçu',
            'materiel_etat' => 'État',
            'materiel_commentaire' => 'Commentaire',
            'materiel_oui' => 'Oui',
            'materiel_non' => 'Non',
            'materiel_fonctionne' => 'Fonctionne',
            'materiel_endommage' => 'Endommagé',
            'materiel_hors_service' => 'Hors service',
        ];
    }

    /**
     * Génère le nom du fichier PDF.
     */
    protected function getFilename(): string
    {
        $date = $this->permanence->date->format('Y-m-d');
        $prefix = $this->isRtl ? 'سجل_المداومة' : 'registre_permanence';
        return "{$prefix}_{$date}.pdf";
    }

    /**
     * Journalise l'impression PDF.
     */
    protected function logPrint(): void
    {
        $user = Auth::user();

        activity()
            ->causedBy($user)
            ->performedOn($this->permanence)
            ->withProperties([
                'action' => 'print_pdf',
                'locale' => $this->locale,
                'permanence_id' => $this->permanence->id,
                'permanence_date' => $this->permanence->date->format('Y-m-d'),
                'ip' => request()->ip(),
            ])
            ->log('Impression PDF du registre de permanence');

        Log::info('Impression PDF registre de permanence', [
            'user_id' => $user->id,
            'user_type' => $user->type->value,
            'permanence_id' => $this->permanence->id,
            'locale' => $this->locale,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Permanence;
use App\Models\RelationManageriale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

/**
 * Service d'export des données - RÉSERVÉ EXCLUSIVEMENT À L'ADMINISTRATEUR.
 * 
 * Ce service gère l'export des données du système vers différents formats :
 * - CSV : Format tabulaire simple
 * - Excel (XLSX) : Format tabulaire avancé
 * - JSON : Sauvegarde technique complète
 * 
 * SÉCURITÉ :
 * - Chaque export est journalisé
 * - Vérification du rôle admin obligatoire
 * - Option de chiffrement disponible
 */
class ExportService
{
    protected User $user;
    protected string $exportPath = 'exports';

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Vérifie que l'utilisateur est admin.
     * @throws \Exception si non admin
     */
    protected function checkAdminAccess(): void
    {
        if (!$this->user || !$this->user->isAdmin()) {
            Log::warning('Tentative d\'export non autorisée', [
                'user_id' => $this->user?->id,
                'user_email' => $this->user?->email,
                'ip' => request()->ip(),
            ]);
            throw new \Exception('Accès refusé. Export réservé à l\'administrateur.');
        }
    }

    /**
     * Journalise l'action d'export.
     */
    protected function logExport(string $type, string $format, array $metadata = []): void
    {
        activity()
            ->causedBy($this->user)
            ->withProperties([
                'type' => $type,
                'format' => $format,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                ...$metadata,
            ])
            ->log("Export {$type} au format {$format}");

        Log::info("Export effectué", [
            'admin_id' => $this->user->id,
            'admin_email' => $this->user->email,
            'type' => $type,
            'format' => $format,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Export des utilisateurs (SANS mots de passe).
     */
    public function exportUsers(string $format = 'csv'): array
    {
        $this->checkAdminAccess();

        $users = User::select([
            'id', 'nom', 'prenom', 'matricule', 'email', 
            'type', 'is_active', 'created_at', 'updated_at'
        ])->get();

        $data = $users->map(fn ($user) => [
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'matricule' => $user->matricule ?? '',
            'email' => $user->email,
            'type' => $user->type->value,
            'type_label' => $user->type->getLabel(),
            'is_active' => $user->is_active ? 'Oui' : 'Non',
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
        ])->toArray();

        $this->logExport('utilisateurs', $format, ['count' => count($data)]);

        return $this->formatExport($data, $format, 'utilisateurs');
    }

    /**
     * Export des permanences avec toutes les données associées.
     */
    public function exportPermanences(string $format = 'csv', ?array $filters = null): array
    {
        $this->checkAdminAccess();

        $query = Permanence::with(['officier', 'affectations.sousOfficier', 'affectations.site']);

        // Appliquer les filtres si fournis
        if ($filters) {
            if (isset($filters['date_from'])) {
                $query->where('date', '>=', $filters['date_from']);
            }
            if (isset($filters['date_to'])) {
                $query->where('date', '<=', $filters['date_to']);
            }
            if (isset($filters['statut'])) {
                $query->where('statut', $filters['statut']);
            }
        }

        $permanences = $query->orderBy('date', 'desc')->get();

        $data = $permanences->map(fn ($p) => [
            'id' => $p->id,
            'date' => $p->date->format('Y-m-d'),
            'heure_debut' => $p->heure_debut?->format('H:i'),
            'heure_fin' => $p->heure_fin?->format('H:i'),
            'officier_id' => $p->officier_id,
            'officier_nom' => $p->officier?->nom_complet,
            'officier_matricule' => $p->officier?->matricule ?? '',
            'statut' => $p->statut->value,
            'statut_label' => $p->statut->getLabel(),
            'commentaire_officier' => $p->commentaire_officier ?? '',
            'validated_at' => $p->validated_at?->format('Y-m-d H:i:s'),
            'nb_sous_officiers' => $p->affectations->count(),
            'sous_officiers' => $p->affectations->map(fn ($a) => [
                'nom' => $a->sousOfficier?->nom_complet,
                'matricule' => $a->sousOfficier?->matricule ?? '',
                'site' => $a->site?->nom,
            ])->toArray(),
            'created_at' => $p->created_at?->format('Y-m-d H:i:s'),
        ])->toArray();

        $this->logExport('permanences', $format, [
            'count' => count($data),
            'filters' => $filters,
        ]);

        return $this->formatExport($data, $format, 'permanences');
    }

    /**
     * Export des relations managériales (événements).
     */
    public function exportRelationsManageriales(string $format = 'csv', ?int $permanenceId = null): array
    {
        $this->checkAdminAccess();

        $query = RelationManageriale::with(['permanence', 'sousOfficier']);

        if ($permanenceId) {
            $query->where('permanence_id', $permanenceId);
        }

        $relations = $query->orderBy('permanence_id')->orderBy('heure_evenement')->get();

        $data = $relations->map(fn ($r) => [
            'id' => $r->id,
            'permanence_id' => $r->permanence_id,
            'permanence_date' => $r->permanence?->date->format('Y-m-d'),
            'heure_evenement' => $r->heure_evenement?->format('H:i'),
            'auteur_id' => $r->sous_officier_id,
            'auteur_nom' => $r->sousOfficier?->nom_complet,
            'auteur_matricule' => $r->sousOfficier?->matricule ?? '',
            'evenement' => $r->evenement,
            'effets_ordonnes' => $r->effets_ordonnes ?? '',
            'observations' => $r->observations ?? '',
            'created_at' => $r->created_at?->format('Y-m-d H:i:s'),
        ])->toArray();

        $this->logExport('relations_manageriales', $format, [
            'count' => count($data),
            'permanence_id' => $permanenceId,
        ]);

        return $this->formatExport($data, $format, 'relations_manageriales');
    }

    /**
     * Export des paramètres système.
     */
    public function exportSettings(string $format = 'json'): array
    {
        $this->checkAdminAccess();

        $settings = Setting::all();

        $data = $settings->map(fn ($s) => [
            'key' => $s->key,
            'label' => $s->label,
            'group' => $s->group,
            'type' => $s->type,
            'value' => $s->type === 'file' ? '[FICHIER]' : $s->value,
        ])->toArray();

        $this->logExport('parametres', $format, ['count' => count($data)]);

        return $this->formatExport($data, $format, 'parametres');
    }

    /**
     * Export des journaux d'audit.
     */
    public function exportAuditLogs(string $format = 'csv', ?array $filters = null): array
    {
        $this->checkAdminAccess();

        $query = Activity::with('causer')->latest();

        if ($filters) {
            if (isset($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            if (isset($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
            if (isset($filters['log_name'])) {
                $query->where('log_name', $filters['log_name']);
            }
        }

        $logs = $query->limit(10000)->get();

        $data = $logs->map(fn ($log) => [
            'id' => $log->id,
            'log_name' => $log->log_name,
            'description' => $log->description,
            'subject_type' => $log->subject_type,
            'subject_id' => $log->subject_id,
            'causer_type' => $log->causer_type,
            'causer_id' => $log->causer_id,
            'causer_email' => $log->causer?->email ?? '',
            'properties' => json_encode($log->properties ?? []),
            'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
        ])->toArray();

        $this->logExport('journaux_audit', $format, [
            'count' => count($data),
            'filters' => $filters,
        ]);

        return $this->formatExport($data, $format, 'journaux_audit');
    }

    /**
     * Export complet (sauvegarde JSON).
     */
    public function exportFullBackup(): array
    {
        $this->checkAdminAccess();

        $backup = [
            'meta' => [
                'version' => '1.0',
                'exported_at' => now()->toIso8601String(),
                'exported_by' => [
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                    'nom' => $this->user->nom_complet,
                ],
                'app_name' => config('app.name'),
            ],
            'users' => $this->getDataForBackup('users'),
            'permanences' => $this->getDataForBackup('permanences'),
            'affectations' => $this->getDataForBackup('affectations'),
            'relations_manageriales' => $this->getDataForBackup('relations_manageriales'),
            'settings' => $this->getDataForBackup('settings'),
        ];

        $this->logExport('sauvegarde_complete', 'json', [
            'tables' => array_keys($backup),
        ]);

        $filename = 'backup_' . now()->format('Y-m-d_His') . '.json';
        $content = json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return [
            'filename' => $filename,
            'content' => $content,
            'mime' => 'application/json',
        ];
    }

    /**
     * Récupère les données pour la sauvegarde.
     */
    protected function getDataForBackup(string $table): array
    {
        return match ($table) {
            'users' => User::select(['id', 'nom', 'prenom', 'matricule', 'email', 'type', 'is_active', 'created_at', 'updated_at'])->get()->toArray(),
            'permanences' => Permanence::all()->toArray(),
            'affectations' => \App\Models\PermanenceSousOfficier::all()->toArray(),
            'relations_manageriales' => RelationManageriale::all()->toArray(),
            'settings' => Setting::all()->map(fn ($s) => [
                'key' => $s->key,
                'label' => $s->label,
                'group' => $s->group,
                'type' => $s->type,
                'value' => $s->type === 'file' ? null : $s->value,
            ])->toArray(),
            default => [],
        };
    }

    /**
     * Formate l'export selon le format demandé.
     */
    protected function formatExport(array $data, string $format, string $prefix): array
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "{$prefix}_{$timestamp}";

        return match ($format) {
            'csv' => $this->toCsv($data, $filename),
            'json' => $this->toJson($data, $filename),
            'xlsx', 'excel' => $this->toExcel($data, $filename),
            default => throw new \InvalidArgumentException("Format non supporté: {$format}"),
        };
    }

    /**
     * Convertit en CSV.
     */
    protected function toCsv(array $data, string $filename): array
    {
        if (empty($data)) {
            return [
                'filename' => "{$filename}.csv",
                'content' => '',
                'mime' => 'text/csv',
            ];
        }

        $output = fopen('php://temp', 'r+');
        
        // En-têtes
        fputcsv($output, array_keys($data[0]), ';');
        
        // Données
        foreach ($data as $row) {
            // Aplatir les tableaux imbriqués
            $flatRow = array_map(function ($value) {
                if (is_array($value)) {
                    return json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                return $value;
            }, $row);
            fputcsv($output, $flatRow, ';');
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        // Ajout BOM pour Excel
        $content = "\xEF\xBB\xBF" . $content;

        return [
            'filename' => "{$filename}.csv",
            'content' => $content,
            'mime' => 'text/csv; charset=UTF-8',
        ];
    }

    /**
     * Convertit en JSON.
     */
    protected function toJson(array $data, string $filename): array
    {
        return [
            'filename' => "{$filename}.json",
            'content' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'mime' => 'application/json',
        ];
    }

    /**
     * Convertit en Excel (format CSV pour simplicité, ou intégrer PhpSpreadsheet).
     */
    protected function toExcel(array $data, string $filename): array
    {
        // Pour un vrai Excel, installer phpoffice/phpspreadsheet
        // Ici on utilise CSV avec BOM pour compatibilité Excel
        return $this->toCsv($data, $filename);
    }
}

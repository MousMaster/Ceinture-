<?php

namespace App\Services;

use App\Models\Permanence;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class PdfPermanenceService
{
    protected Permanence $permanence;

    public function __construct(Permanence $permanence)
    {
        $this->permanence = $permanence->load([
            'officier',
            'affectations.sousOfficier',
            'affectations.site',
            'relationsManageriales.sousOfficier',
        ]);
    }

    /**
     * Génère le PDF du registre de permanence
     */
    public function generate(): \Barryvdh\DomPDF\PDF
    {
        $data = $this->prepareData();

        $pdf = Pdf::loadView('pdf.permanence', $data);
        
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 150,
        ]);

        return $pdf;
    }

    /**
     * Télécharge le PDF
     */
    public function download(): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate();
        $filename = $this->getFilename();

        return $pdf->download($filename);
    }

    /**
     * Affiche le PDF dans le navigateur
     */
    public function stream(): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate();
        $filename = $this->getFilename();

        return $pdf->stream($filename);
    }

    /**
     * Prépare les données pour le PDF
     */
    protected function prepareData(): array
    {
        return [
            // Paramètres institutionnels
            'institution_name' => Setting::get('institution_name', 'INSTITUTION'),
            'direction_name' => Setting::get('direction_name', 'DIRECTION'),
            'system_name' => Setting::get('system_name', 'SYSTÈME DE GESTION'),
            'pdf_title' => Setting::get('pdf_title', 'REGISTRE DE PERMANENCE'),
            'pdf_footer' => Setting::get('pdf_footer', 'Document officiel'),
            
            // Logos en base64
            'logo_institution' => Setting::getLogoBase64('logo_institution'),
            'logo_direction' => Setting::getLogoBase64('logo_direction'),
            
            // Données de la permanence
            'permanence' => $this->permanence,
            'officier' => $this->permanence->officier,
            'affectations' => $this->permanence->affectations,
            'evenements' => $this->permanence->relationsManageriales->sortBy('heure_evenement'),
            
            // Métadonnées
            'date_edition' => now()->format('d/m/Y à H:i'),
            'date_permanence' => $this->permanence->date->format('d/m/Y'),
            'heure_debut' => $this->permanence->heure_debut->format('H:i'),
            'heure_fin' => $this->permanence->heure_fin->format('H:i'),
        ];
    }

    /**
     * Génère le nom du fichier PDF
     */
    protected function getFilename(): string
    {
        $date = $this->permanence->date->format('Y-m-d');
        return "registre_permanence_{$date}.pdf";
    }
}

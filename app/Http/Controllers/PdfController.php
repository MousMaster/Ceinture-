<?php

namespace App\Http\Controllers;

use App\Models\Permanence;
use App\Services\PdfPermanenceService;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    /**
     * Télécharge le PDF d'une permanence
     */
    public function downloadPermanence(Permanence $permanence)
    {
        // Vérifier les permissions
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->canManagePermanence($permanence)) {
            abort(403, 'Vous n\'avez pas les droits pour imprimer ce registre.');
        }

        $service = new PdfPermanenceService($permanence);
        return $service->download();
    }

    /**
     * Affiche le PDF dans le navigateur
     */
    public function streamPermanence(Permanence $permanence)
    {
        // Vérifier les permissions
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->canManagePermanence($permanence)) {
            abort(403, 'Vous n\'avez pas les droits pour imprimer ce registre.');
        }

        $service = new PdfPermanenceService($permanence);
        return $service->stream();
    }
}

<?php

namespace App\Policies;

use App\Models\ReceptionMateriel;
use App\Models\Permanence;
use App\Models\User;

/**
 * Policy pour la réception du matériel.
 * 
 * CLOISONNEMENT PAR RÔLE :
 * - Admin : accès complet
 * - Officier : gestion du matériel de ses permanences
 * - Sous-officier : AUCUNE action (seuls officier/admin saisissent)
 * - Viewer : LECTURE SEULE
 */
class ReceptionMaterielPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Viewer peut voir (lecture seule)
        if ($user->isViewer()) {
            return true;
        }

        // Admin et Officier voient tout
        return $user->isAdmin() || $user->isOfficier();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ReceptionMateriel $reception): bool
    {
        // Viewer peut tout voir (lecture seule)
        if ($user->isViewer()) {
            return true;
        }

        // Admin peut tout voir
        if ($user->isAdmin()) {
            return true;
        }

        // Officier peut voir les réceptions de ses permanences
        if ($user->isOfficier()) {
            return $reception->permanence->officier_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * Viewer et Sous-officier JAMAIS autorisés.
     */
    public function create(User $user): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

        // Sous-officier JAMAIS autorisé (c'est l'officier qui saisit)
        if ($user->isSousOfficier()) {
            return false;
        }

        // Admin et Officier peuvent créer
        return $user->isAdmin() || $user->isOfficier();
    }

    /**
     * Check if user can create for a specific permanence.
     */
    public function createForPermanence(User $user, Permanence $permanence): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

        // Sous-officier JAMAIS autorisé
        if ($user->isSousOfficier()) {
            return false;
        }

        // Permanence verrouillée : non modifiable (sauf admin)
        if ($permanence->isLocked() && !$user->isAdmin()) {
            return false;
        }

        // Admin peut toujours
        if ($user->isAdmin()) {
            return true;
        }

        // Officier peut créer pour ses permanences
        if ($user->isOfficier()) {
            return $permanence->officier_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Viewer et Sous-officier JAMAIS autorisés.
     */
    public function update(User $user, ReceptionMateriel $reception): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

        // Sous-officier JAMAIS autorisé
        if ($user->isSousOfficier()) {
            return false;
        }

        // Permanence verrouillée : non modifiable (sauf admin)
        if ($reception->permanence->isLocked() && !$user->isAdmin()) {
            return false;
        }

        // Admin peut tout modifier
        if ($user->isAdmin()) {
            return true;
        }

        // Officier peut modifier les réceptions de ses permanences
        if ($user->isOfficier()) {
            return $reception->permanence->officier_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Viewer et Sous-officier JAMAIS autorisés.
     */
    public function delete(User $user, ReceptionMateriel $reception): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

        // Sous-officier JAMAIS autorisé
        if ($user->isSousOfficier()) {
            return false;
        }

        // Permanence verrouillée : non supprimable (sauf admin)
        if ($reception->permanence->isLocked() && !$user->isAdmin()) {
            return false;
        }

        // Admin peut supprimer
        if ($user->isAdmin()) {
            return true;
        }

        // Officier peut supprimer les réceptions de ses permanences
        if ($user->isOfficier()) {
            return $reception->permanence->officier_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ReceptionMateriel $reception): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ReceptionMateriel $reception): bool
    {
        return $user->isAdmin();
    }
}

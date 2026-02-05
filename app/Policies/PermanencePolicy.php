<?php

namespace App\Policies;

use App\Enums\StatutPermanence;
use App\Models\Permanence;
use App\Models\User;

class PermanencePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin et Officier voient toutes les permanences
        if ($user->isAdmin() || $user->isOfficier()) {
            return true;
        }

        // Sous-officier ne voit que celles auxquelles il est affecté
        return $user->isSousOfficier();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permanence $permanence): bool
    {
        // Admin et Officier peuvent tout voir
        if ($user->isAdmin() || $user->isOfficier()) {
            return true;
        }

        // Sous-officier ne peut voir que s'il est affecté
        if ($user->isSousOfficier()) {
            return $user->isAffectedToPermanence($permanence);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Seuls Admin et Officier peuvent créer des permanences
        return $user->isAdmin() || $user->isOfficier();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permanence $permanence): bool
    {
        // Une permanence validée ne peut plus être modifiée (sauf admin)
        if ($permanence->isLocked() && !$user->isAdmin()) {
            return false;
        }

        // Admin peut tout modifier
        if ($user->isAdmin()) {
            return true;
        }

        // Officier peut modifier les permanences dont il est responsable
        if ($user->isOfficier()) {
            return $permanence->officier_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permanence $permanence): bool
    {
        // Seul l'admin peut supprimer une permanence
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Permanence $permanence): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permanence $permanence): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can validate the model.
     */
    public function validate(User $user, Permanence $permanence): bool
    {
        // Ne peut pas valider une permanence déjà validée
        if ($permanence->isLocked()) {
            return false;
        }

        // Admin peut toujours valider
        if ($user->isAdmin()) {
            return true;
        }

        // Seul l'officier responsable peut valider
        if ($user->isOfficier()) {
            return $permanence->officier_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can start the permanence.
     */
    public function start(User $user, Permanence $permanence): bool
    {
        // Ne peut démarrer qu'une permanence planifiée
        if ($permanence->statut !== StatutPermanence::Planifiee) {
            return false;
        }

        // Admin peut toujours démarrer
        if ($user->isAdmin()) {
            return true;
        }

        // Seul l'officier responsable peut démarrer
        if ($user->isOfficier()) {
            return $permanence->officier_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can reopen the model (only admin).
     */
    public function reopen(User $user, Permanence $permanence): bool
    {
        // Seul l'admin peut rouvrir une permanence validée
        return $user->isAdmin() && $permanence->isLocked();
    }
}

<?php

namespace App\Policies;

use App\Models\Permanence;
use App\Models\RelationManageriale;
use App\Models\User;

class RelationManagerialePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RelationManageriale $relation): bool
    {
        // Admin et Officier peuvent tout voir
        if ($user->isAdmin() || $user->isOfficier()) {
            return true;
        }

        // Sous-officier ne peut voir que s'il est affecté à la permanence
        if ($user->isSousOfficier()) {
            return $user->isAffectedToPermanence($relation->permanence);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin et Officier peuvent créer
        if ($user->isAdmin() || $user->isOfficier()) {
            return true;
        }

        // Sous-officier peut créer s'il est affecté à au moins une permanence non validée
        if ($user->isSousOfficier()) {
            return $user->affectations()
                ->whereHas('permanence', function ($query) {
                    $query->where('statut', '!=', 'validee');
                })
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create for a specific permanence.
     */
    public function createFor(User $user, Permanence $permanence): bool
    {
        // Permanence validée = pas de création (sauf admin)
        if ($permanence->isLocked() && !$user->isAdmin()) {
            return false;
        }

        // Admin peut toujours créer
        if ($user->isAdmin()) {
            return true;
        }

        // Officier responsable peut créer
        if ($user->isOfficier() && $permanence->officier_id === $user->id) {
            return true;
        }

        // Sous-officier affecté peut créer
        if ($user->isSousOfficier()) {
            return $user->isAffectedToPermanence($permanence);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RelationManageriale $relation): bool
    {
        return $relation->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RelationManageriale $relation): bool
    {
        return $relation->canBeDeletedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RelationManageriale $relation): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RelationManageriale $relation): bool
    {
        return $user->isAdmin();
    }
}

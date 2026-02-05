<?php

namespace App\Policies;

use App\Models\Appareil;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy pour la gestion des appareils.
 * 
 * Règles :
 * - Admin : accès complet
 * - Officier : lecture + création/modification si autorisé
 * - Sous-officier : lecture seule (pour sélection dans les relevés)
 */
class AppareilPolicy
{
    use HandlesAuthorization;

    /**
     * Voir la liste des appareils.
     */
    public function viewAny(User $user): bool
    {
        // Tous les utilisateurs peuvent voir la liste
        return true;
    }

    /**
     * Voir un appareil spécifique.
     */
    public function view(User $user, Appareil $appareil): bool
    {
        return true;
    }

    /**
     * Créer un appareil.
     * Admin et officier uniquement.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOfficier();
    }

    /**
     * Modifier un appareil.
     * Admin et officier uniquement.
     */
    public function update(User $user, Appareil $appareil): bool
    {
        return $user->isAdmin() || $user->isOfficier();
    }

    /**
     * Supprimer un appareil.
     * Admin uniquement.
     */
    public function delete(User $user, Appareil $appareil): bool
    {
        return $user->isAdmin();
    }

    /**
     * Restaurer un appareil supprimé.
     */
    public function restore(User $user, Appareil $appareil): bool
    {
        return $user->isAdmin();
    }

    /**
     * Supprimer définitivement un appareil.
     */
    public function forceDelete(User $user, Appareil $appareil): bool
    {
        return $user->isAdmin();
    }
}

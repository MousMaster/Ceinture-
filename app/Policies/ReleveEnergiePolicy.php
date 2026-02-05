<?php

namespace App\Policies;

use App\Models\Permanence;
use App\Models\ReleveEnergie;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy pour les relevés d'énergie.
 * 
 * CLOISONNEMENT STRICT :
 * - Sous-officier : voit UNIQUEMENT ses propres relevés
 * - Officier : lecture seule (optionnel après validation)
 * - Admin : accès complet
 * - Viewer : LECTURE SEULE sur tout
 * - Lecture seule après validation de la permanence
 */
class ReleveEnergiePolicy
{
    use HandlesAuthorization;

    /**
     * Voir la liste des relevés.
     * Le scope visibleBy() dans le model gère le cloisonnement.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Voir un relevé spécifique.
     */
    public function view(User $user, ReleveEnergie $releve): bool
    {
        // Viewer peut tout voir (lecture seule)
        if ($user->isViewer()) {
            return true;
        }

        // Admin voit tout
        if ($user->isAdmin()) {
            return true;
        }

        // Officier peut voir (lecture seule)
        if ($user->isOfficier()) {
            return true;
        }

        // Sous-officier : UNIQUEMENT ses propres relevés
        if ($user->isSousOfficier()) {
            return $releve->sous_officier_id === $user->id;
        }

        return false;
    }

    /**
     * Créer un relevé.
     * Viewer JAMAIS autorisé.
     */
    public function create(User $user, ?Permanence $permanence = null): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

        // Admin peut toujours créer
        if ($user->isAdmin()) {
            return true;
        }

        // Si permanence fournie, vérifier qu'elle n'est pas validée
        if ($permanence && $permanence->isLocked()) {
            return false;
        }

        // Sous-officier doit être affecté à la permanence
        if ($user->isSousOfficier()) {
            if ($permanence) {
                return $permanence->sousOfficiers()->where('users.id', $user->id)->exists();
            }
            return true; // Sera vérifié au niveau du RelationManager
        }

        return false;
    }

    /**
     * Modifier un relevé.
     * Viewer JAMAIS autorisé.
     */
    public function update(User $user, ReleveEnergie $releve): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }
        return $releve->canBeEditedBy($user);
    }

    /**
     * Supprimer un relevé.
     * Viewer JAMAIS autorisé.
     */
    public function delete(User $user, ReleveEnergie $releve): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }
        return $releve->canBeDeletedBy($user);
    }

    /**
     * Restaurer un relevé supprimé.
     */
    public function restore(User $user, ReleveEnergie $releve): bool
    {
        return $user->isAdmin();
    }

    /**
     * Supprimer définitivement un relevé.
     */
    public function forceDelete(User $user, ReleveEnergie $releve): bool
    {
        return $user->isAdmin();
    }

    // ========== MÉTHODES SPÉCIFIQUES AU CLOISONNEMENT ==========

    /**
     * Vérifie si l'utilisateur peut voir la section relevés d'énergie.
     * Visible pour sous-officier (ses données), admin/officier (lecture), Viewer.
     */
    public function viewSection(User $user, ?Permanence $permanence = null): bool
    {
        // Viewer voit toujours (lecture seule)
        if ($user->isViewer()) {
            return true;
        }

        // Admin voit toujours
        if ($user->isAdmin()) {
            return true;
        }

        // Officier voit (lecture seule après validation)
        if ($user->isOfficier()) {
            return true;
        }

        // Sous-officier voit s'il est affecté
        if ($user->isSousOfficier() && $permanence) {
            return $permanence->sousOfficiers()->where('users.id', $user->id)->exists();
        }

        return $user->isSousOfficier();
    }

    /**
     * Vérifie si l'utilisateur peut éditer dans la section.
     * Désactivé si permanence validée. Viewer JAMAIS autorisé.
     */
    public function editInSection(User $user, ?Permanence $permanence = null): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

        // Admin peut toujours éditer
        if ($user->isAdmin()) {
            return true;
        }

        // Permanence validée = lecture seule
        if ($permanence && $permanence->isLocked()) {
            return false;
        }

        // Sous-officier peut éditer ses relevés
        return $user->isSousOfficier();
    }
}

<?php

namespace App\Policies;

use App\Models\Permanence;
use App\Models\RedemarrageAppareil;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy pour les redémarrages d'appareils.
 * 
 * CLOISONNEMENT STRICT :
 * - Sous-officier : AUCUN ACCÈS (totalement invisible)
 * - Officier : accès uniquement à ses permanences
 * - Admin : accès complet
 * - Viewer : LECTURE SEULE
 * - Verrouillé après validation de la permanence
 */
class RedemarrageAppareilPolicy
{
    use HandlesAuthorization;

    /**
     * Voir la liste des redémarrages.
     * INVISIBLE pour sous-officier. Viewer peut voir.
     */
    public function viewAny(User $user): bool
    {
        // Sous-officier : AUCUN ACCÈS
        if ($user->isSousOfficier()) {
            return false;
        }

        // Viewer peut voir (lecture seule)
        if ($user->isViewer()) {
            return true;
        }

        return $user->isAdmin() || $user->isOfficier();
    }

    /**
     * Voir un redémarrage spécifique.
     */
    public function view(User $user, RedemarrageAppareil $redemarrage): bool
    {
        // Viewer peut voir (lecture seule)
        if ($user->isViewer()) {
            return true;
        }
        return $redemarrage->canBeViewedBy($user);
    }

    /**
     * Créer un redémarrage.
     * Sous-officier et Viewer JAMAIS autorisés.
     */
    public function create(User $user, ?Permanence $permanence = null): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

        // Sous-officier : JAMAIS autorisé
        if ($user->isSousOfficier()) {
            return false;
        }

        // Admin peut toujours créer
        if ($user->isAdmin()) {
            return true;
        }

        // Permanence validée = pas de création
        if ($permanence && $permanence->isLocked()) {
            return false;
        }

        // Officier responsable peut créer
        if ($user->isOfficier() && $permanence) {
            return $permanence->officier_id === $user->id;
        }

        return $user->isOfficier();
    }

    /**
     * Modifier un redémarrage.
     * Viewer JAMAIS autorisé.
     */
    public function update(User $user, RedemarrageAppareil $redemarrage): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }
        return $redemarrage->canBeEditedBy($user);
    }

    /**
     * Supprimer un redémarrage.
     * Viewer JAMAIS autorisé.
     */
    public function delete(User $user, RedemarrageAppareil $redemarrage): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }
        return $redemarrage->canBeDeletedBy($user);
    }

    /**
     * Restaurer un redémarrage supprimé.
     */
    public function restore(User $user, RedemarrageAppareil $redemarrage): bool
    {
        return $user->isAdmin();
    }

    /**
     * Supprimer définitivement un redémarrage.
     */
    public function forceDelete(User $user, RedemarrageAppareil $redemarrage): bool
    {
        return $user->isAdmin();
    }

    // ========== MÉTHODES SPÉCIFIQUES AU CLOISONNEMENT ==========

    /**
     * Vérifie si l'utilisateur peut voir la section redémarrages.
     * INVISIBLE pour sous-officier. Viewer peut voir.
     */
    public function viewSection(User $user, ?Permanence $permanence = null): bool
    {
        // Sous-officier : JAMAIS visible
        if ($user->isSousOfficier()) {
            return false;
        }

        // Viewer peut voir (lecture seule)
        if ($user->isViewer()) {
            return true;
        }

        // Admin voit toujours
        if ($user->isAdmin()) {
            return true;
        }

        // Officier voit ses propres permanences
        if ($user->isOfficier() && $permanence) {
            return $permanence->officier_id === $user->id;
        }

        return $user->isOfficier();
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

        // Sous-officier : JAMAIS autorisé
        if ($user->isSousOfficier()) {
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

        // Officier responsable peut éditer
        if ($user->isOfficier() && $permanence) {
            return $permanence->officier_id === $user->id;
        }

        return $user->isOfficier();
    }
}

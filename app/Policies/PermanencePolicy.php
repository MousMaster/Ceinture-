<?php

namespace App\Policies;

use App\Enums\StatutPermanence;
use App\Models\Permanence;
use App\Models\User;

/**
 * Policy pour les permanences.
 * 
 * CLOISONNEMENT PAR RÔLE :
 * - Admin : accès complet
 * - Officier : gestion de ses permanences
 * - Sous-officier : saisie de ses propres données
 * - Viewer : LECTURE SEULE sur tout
 */
class PermanencePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Viewer peut voir toutes les permanences (lecture seule)
        if ($user->isViewer()) {
            return true;
        }

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
        // Viewer peut tout voir (lecture seule)
        if ($user->isViewer()) {
            return true;
        }

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
     * Viewer JAMAIS autorisé.
     */
    public function create(User $user): bool
    {
        // Viewer JAMAIS autorisé à créer
        if ($user->isViewer()) {
            return false;
        }

        // Seuls Admin et Officier peuvent créer des permanences
        return $user->isAdmin() || $user->isOfficier();
    }

    /**
     * Determine whether the user can update the model.
     * Viewer JAMAIS autorisé.
     */
    public function update(User $user, Permanence $permanence): bool
    {
        // Viewer JAMAIS autorisé à modifier
        if ($user->isViewer()) {
            return false;
        }

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
     * Viewer JAMAIS autorisé.
     */
    public function delete(User $user, Permanence $permanence): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

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
     * Viewer JAMAIS autorisé.
     */
    public function validate(User $user, Permanence $permanence): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

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
     * Viewer JAMAIS autorisé.
     */
    public function start(User $user, Permanence $permanence): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

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
     * Viewer JAMAIS autorisé.
     */
    public function reopen(User $user, Permanence $permanence): bool
    {
        // Viewer JAMAIS autorisé
        if ($user->isViewer()) {
            return false;
        }

        // Seul l'admin peut rouvrir une permanence validée
        return $user->isAdmin() && $permanence->isLocked();
    }

    /**
     * Determine whether the user can print PDF of the permanence.
     * Règles strictes :
     * - Permanence DOIT être validée
     * - Viewer peut imprimer (lecture seule)
     * - Admin et Officier responsable peuvent imprimer
     * - Sous-officier JAMAIS autorisé (même s'il est affecté)
     */
    public function printPdf(User $user, Permanence $permanence): bool
    {
        // Règle absolue : permanence doit être validée
        if (!$permanence->isLocked()) {
            return false;
        }

        // Sous-officier JAMAIS autorisé à imprimer
        if ($user->isSousOfficier()) {
            return false;
        }

        // Viewer peut imprimer (lecture seule)
        if ($user->isViewer()) {
            return true;
        }

        // Admin peut toujours imprimer une permanence validée
        if ($user->isAdmin()) {
            return true;
        }

        // Officier peut imprimer uniquement s'il est responsable
        if ($user->isOfficier()) {
            return $permanence->officier_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can export data.
     * Règle STRICTE : SEUL l'administrateur peut exporter.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view officer section.
     * Cloisonnement strict : sous-officier NE VOIT JAMAIS la partie officier.
     * Viewer peut voir (lecture seule).
     */
    public function viewOfficerSection(User $user, Permanence $permanence): bool
    {
        // Sous-officier JAMAIS accès à la section officier
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
     * Determine whether the user can view validation section.
     * Viewer peut voir (lecture seule).
     */
    public function viewValidation(User $user, Permanence $permanence): bool
    {
        // Sous-officier ne voit jamais la section validation
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
     * Determine whether the user can view other sous-officiers.
     * Cloisonnement : sous-officier ne voit que lui-même.
     * Viewer peut voir tout (lecture seule).
     */
    public function viewOtherSousOfficiers(User $user, Permanence $permanence): bool
    {
        // Sous-officier ne voit JAMAIS les autres sous-officiers
        if ($user->isSousOfficier()) {
            return false;
        }

        // Viewer peut voir tout
        return true;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RelationManageriale extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'relations_manageriales';

    protected $fillable = [
        'permanence_id',
        'sous_officier_id',
        'heure_evenement',
        'evenement',
        'effets_ordonnes',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'heure_evenement' => 'datetime:H:i',
        ];
    }

    // ========== RELATIONS ==========

    public function permanence(): BelongsTo
    {
        return $this->belongsTo(Permanence::class);
    }

    public function sousOfficier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sous_officier_id');
    }

    // ========== HELPERS ==========

    /**
     * Vérifie si l'événement peut être modifié par un utilisateur
     */
    public function canBeEditedBy(User $user): bool
    {
        // Si la permanence est validée, personne ne peut modifier (sauf admin)
        if ($this->permanence->isLocked()) {
            return $user->isAdmin();
        }

        // Admin peut tout modifier
        if ($user->isAdmin()) {
            return true;
        }

        // Officier responsable peut modifier
        if ($user->isOfficier() && $this->permanence->officier_id === $user->id) {
            return true;
        }

        // Sous-officier ne peut modifier que ses propres saisies
        if ($user->isSousOfficier()) {
            return $this->sous_officier_id === $user->id;
        }

        return false;
    }

    /**
     * Vérifie si l'événement peut être supprimé par un utilisateur
     */
    public function canBeDeletedBy(User $user): bool
    {
        // Si la permanence est validée, seul admin peut supprimer
        if ($this->permanence->isLocked()) {
            return $user->isAdmin();
        }

        // Admin peut tout supprimer
        if ($user->isAdmin()) {
            return true;
        }

        // Officier responsable peut supprimer
        if ($user->isOfficier() && $this->permanence->officier_id === $user->id) {
            return true;
        }

        // Sous-officier ne peut supprimer que ses propres saisies
        if ($user->isSousOfficier()) {
            return $this->sous_officier_id === $user->id;
        }

        return false;
    }

    // ========== SCOPES ==========

    /**
     * Scope pour filtrer les événements visibles par un utilisateur.
     * CLOISONNEMENT STRICT :
     * - Admin/Officier : tous les événements
     * - Sous-officier : UNIQUEMENT ses propres saisies
     */
    public function scopeVisibleBy($query, User $user)
    {
        // Admin voit tout
        if ($user->isAdmin()) {
            return $query;
        }

        // Officier voit tout
        if ($user->isOfficier()) {
            return $query;
        }

        // CLOISONNEMENT STRICT : Sous-officier voit UNIQUEMENT ses propres saisies
        if ($user->isSousOfficier()) {
            return $query->where('sous_officier_id', $user->id);
        }

        // Sécurité : aucun résultat par défaut
        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope pour filtrer par permanence.
     */
    public function scopeForPermanence($query, Permanence $permanence)
    {
        return $query->where('permanence_id', $permanence->id);
    }

    /**
     * Scope pour filtrer par auteur (sous-officier).
     */
    public function scopeByAuthor($query, User $user)
    {
        return $query->where('sous_officier_id', $user->id);
    }

    // ========== ACTIVITY LOG ==========

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['permanence_id', 'sous_officier_id', 'heure_evenement', 'evenement', 'effets_ordonnes', 'observations'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

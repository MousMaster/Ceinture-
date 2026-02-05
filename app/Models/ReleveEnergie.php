<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Modèle ReleveEnergie - Suivi du pourcentage d'énergie des appareils.
 * 
 * CLOISONNEMENT STRICT :
 * - Le sous-officier voit UNIQUEMENT ses propres relevés
 * - Lecture seule après validation de la permanence
 */
class ReleveEnergie extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'releves_energie';

    protected $fillable = [
        'permanence_id',
        'appareil_id',
        'sous_officier_id',
        'pourcentage_energie',
        'heure_releve',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'heure_releve' => 'datetime:H:i',
            'pourcentage_energie' => 'integer',
        ];
    }

    // ========== RELATIONS ==========

    public function permanence(): BelongsTo
    {
        return $this->belongsTo(Permanence::class);
    }

    public function appareil(): BelongsTo
    {
        return $this->belongsTo(Appareil::class);
    }

    public function sousOfficier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sous_officier_id');
    }

    // ========== HELPERS ==========

    /**
     * Vérifie si le relevé peut être modifié par un utilisateur.
     */
    public function canBeEditedBy(User $user): bool
    {
        // Permanence validée = lecture seule (sauf admin)
        if ($this->permanence->isLocked()) {
            return $user->isAdmin();
        }

        // Admin peut tout modifier
        if ($user->isAdmin()) {
            return true;
        }

        // Sous-officier : uniquement ses propres relevés
        if ($user->isSousOfficier()) {
            return $this->sous_officier_id === $user->id;
        }

        return false;
    }

    /**
     * Vérifie si le relevé peut être supprimé par un utilisateur.
     */
    public function canBeDeletedBy(User $user): bool
    {
        // Même logique que pour l'édition
        return $this->canBeEditedBy($user);
    }

    // ========== SCOPES ==========

    /**
     * CLOISONNEMENT STRICT : Relevés visibles par un utilisateur.
     * - Admin/Officier : tous les relevés (lecture seule pour officier)
     * - Sous-officier : UNIQUEMENT ses propres relevés
     */
    public function scopeVisibleBy($query, User $user)
    {
        // Admin voit tout
        if ($user->isAdmin()) {
            return $query;
        }

        // Officier voit tout (lecture seule dans le RelationManager)
        if ($user->isOfficier()) {
            return $query;
        }

        // CLOISONNEMENT : Sous-officier voit UNIQUEMENT ses propres relevés
        if ($user->isSousOfficier()) {
            return $query->where('sous_officier_id', $user->id);
        }

        // Sécurité : aucun résultat par défaut
        return $query->whereRaw('1 = 0');
    }

    /**
     * Filtrer par permanence.
     */
    public function scopeForPermanence($query, Permanence $permanence)
    {
        return $query->where('permanence_id', $permanence->id);
    }

    /**
     * Filtrer par auteur (sous-officier).
     */
    public function scopeByAuthor($query, User $user)
    {
        return $query->where('sous_officier_id', $user->id);
    }

    // ========== ACTIVITY LOG ==========

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['permanence_id', 'appareil_id', 'sous_officier_id', 'pourcentage_energie', 'heure_releve', 'observations'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

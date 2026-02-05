<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Modèle RedemarrageAppareil - Suivi des redémarrages d'appareils.
 * 
 * CLOISONNEMENT STRICT :
 * - Totalement INVISIBLE pour les sous-officiers
 * - Visible uniquement par officier responsable et admin
 * - Verrouillé après validation de la permanence
 */
class RedemarrageAppareil extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'redemarrages_appareils';

    protected $fillable = [
        'permanence_id',
        'appareil_id',
        'officier_id',
        'nombre_redemarrages',
        'motif',
        'heure_debut',
        'heure_fin',
        'decision_officier',
    ];

    protected function casts(): array
    {
        return [
            'heure_debut' => 'datetime:H:i',
            'heure_fin' => 'datetime:H:i',
            'nombre_redemarrages' => 'integer',
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

    public function officier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officier_id');
    }

    // ========== HELPERS ==========

    /**
     * Vérifie si le redémarrage peut être modifié par un utilisateur.
     * IMPORTANT : Sous-officier n'a JAMAIS accès.
     */
    public function canBeEditedBy(User $user): bool
    {
        // Sous-officier JAMAIS autorisé
        if ($user->isSousOfficier()) {
            return false;
        }

        // Permanence validée = lecture seule (sauf admin)
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

        return false;
    }

    /**
     * Vérifie si le redémarrage peut être supprimé par un utilisateur.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
    }

    /**
     * Vérifie si le redémarrage peut être vu par un utilisateur.
     * IMPORTANT : Sous-officier n'a JAMAIS accès.
     */
    public function canBeViewedBy(User $user): bool
    {
        // Sous-officier JAMAIS autorisé
        if ($user->isSousOfficier()) {
            return false;
        }

        // Admin voit tout
        if ($user->isAdmin()) {
            return true;
        }

        // Officier responsable voit
        if ($user->isOfficier() && $this->permanence->officier_id === $user->id) {
            return true;
        }

        return false;
    }

    // ========== SCOPES ==========

    /**
     * CLOISONNEMENT STRICT : Redémarrages visibles par un utilisateur.
     * - Admin : tous les redémarrages
     * - Officier : uniquement ses permanences
     * - Sous-officier : AUCUN ACCÈS
     */
    public function scopeVisibleBy($query, User $user)
    {
        // Sous-officier : AUCUN ACCÈS
        if ($user->isSousOfficier()) {
            return $query->whereRaw('1 = 0');
        }

        // Admin voit tout
        if ($user->isAdmin()) {
            return $query;
        }

        // Officier : uniquement ses permanences
        if ($user->isOfficier()) {
            return $query->whereHas('permanence', function ($q) use ($user) {
                $q->where('officier_id', $user->id);
            });
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
     * Filtrer par auteur (officier).
     */
    public function scopeByAuthor($query, User $user)
    {
        return $query->where('officier_id', $user->id);
    }

    // ========== ACTIVITY LOG ==========

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['permanence_id', 'appareil_id', 'officier_id', 'nombre_redemarrages', 'motif', 'heure_debut', 'heure_fin', 'decision_officier'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

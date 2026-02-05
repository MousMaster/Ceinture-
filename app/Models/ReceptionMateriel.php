<?php

namespace App\Models;

use App\Enums\EtatFonctionnement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Modèle ReceptionMateriel - Suivi de la réception du matériel.
 * Enregistre l'état de chaque appareil reçu par une personne lors d'une permanence.
 */
class ReceptionMateriel extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'reception_materiels';

    protected $fillable = [
        'permanence_id',
        'user_id',
        'appareil_id',
        'recu_integralite',
        'etat_fonctionnement',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'recu_integralite' => 'boolean',
            'etat_fonctionnement' => EtatFonctionnement::class,
        ];
    }

    // ========== RELATIONS ==========

    /**
     * Permanence associée.
     */
    public function permanence(): BelongsTo
    {
        return $this->belongsTo(Permanence::class);
    }

    /**
     * Utilisateur ayant reçu le matériel.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Appareil concerné.
     */
    public function appareil(): BelongsTo
    {
        return $this->belongsTo(Appareil::class);
    }

    // ========== ACCESSORS ==========

    /**
     * Libellé de l'état de réception.
     */
    public function getRecuLabelAttribute(): string
    {
        return $this->recu_integralite
            ? __('materiel.recu.oui')
            : __('materiel.recu.non');
    }

    // ========== SCOPES ==========

    /**
     * Réceptions d'une permanence.
     */
    public function scopeForPermanence($query, int $permanenceId)
    {
        return $query->where('permanence_id', $permanenceId);
    }

    /**
     * Réceptions d'un utilisateur.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Réceptions par un officier.
     */
    public function scopeByOfficier($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('type', 'officier'));
    }

    /**
     * Réceptions par un sous-officier opérateur.
     */
    public function scopeByOperateur($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('type', 'sous_officier')
              ->where('fonction', 'operateur');
        });
    }

    /**
     * Scope de cloisonnement selon l'utilisateur connecté.
     */
    public function scopeVisibleBy($query, User $user)
    {
        // Admin et Viewer voient tout
        if ($user->isAdmin() || $user->isViewer()) {
            return $query;
        }

        // Officier : ses permanences
        if ($user->isOfficier()) {
            return $query->whereHas('permanence', fn ($q) => $q->where('officier_id', $user->id));
        }

        // Sous-officier : uniquement les siennes
        if ($user->isSousOfficier()) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    // ========== ACTIVITY LOG ==========

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['permanence_id', 'user_id', 'appareil_id', 'recu_integralite', 'etat_fonctionnement', 'commentaire'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

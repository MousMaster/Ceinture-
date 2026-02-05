<?php

namespace App\Models;

use App\Enums\DestinataireAppareil;
use App\Enums\StatutAppareil;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Modèle Appareil - Liste dynamique des appareils.
 * Paramétrable par admin et officier autorisé.
 * Partagé entre officiers et sous-officiers pour les relevés.
 */
class Appareil extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nom',
        'type',
        'categorie',
        'destinataire',
        'numero_serie',
        'site_id',
        'statut',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'destinataire' => DestinataireAppareil::class,
            'statut' => StatutAppareil::class,
            'is_active' => 'boolean',
        ];
    }

    // ========== RELATIONS ==========

    /**
     * Site associé à l'appareil (optionnel).
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Relevés d'énergie de cet appareil.
     */
    public function relevesEnergie(): HasMany
    {
        return $this->hasMany(ReleveEnergie::class);
    }

    /**
     * Redémarrages de cet appareil.
     */
    public function redemarrages(): HasMany
    {
        return $this->hasMany(RedemarrageAppareil::class);
    }

    /**
     * Réceptions de cet appareil.
     */
    public function receptions(): HasMany
    {
        return $this->hasMany(ReceptionMateriel::class);
    }

    // ========== ACCESSORS ==========

    /**
     * Nom complet avec site si applicable.
     */
    public function getNomCompletAttribute(): string
    {
        $nom = $this->nom;
        if ($this->site) {
            $nom .= ' (' . $this->site->nom . ')';
        }
        return $nom;
    }

    // ========== SCOPES ==========

    /**
     * Appareils actifs uniquement.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Appareils en service.
     */
    public function scopeEnService($query)
    {
        return $query->where('statut', StatutAppareil::Actif);
    }

    /**
     * Appareils d'un site spécifique.
     */
    public function scopeForSite($query, $siteId)
    {
        return $query->where(function ($q) use ($siteId) {
            $q->where('site_id', $siteId)
              ->orWhereNull('site_id'); // Les appareils sans site sont globaux
        });
    }

    /**
     * Appareils destinés aux officiers.
     */
    public function scopeForOfficier($query)
    {
        return $query->where('destinataire', DestinataireAppareil::Officier);
    }

    /**
     * Appareils destinés aux opérateurs.
     */
    public function scopeForOperateur($query)
    {
        return $query->where('destinataire', DestinataireAppareil::Operateur);
    }

    /**
     * Appareils visibles par un utilisateur.
     * Si sous-officier avec affectation site, filtre par site.
     */
    public function scopeVisibleBy($query, User $user, ?int $siteId = null)
    {
        // Admin et officier voient tout
        if ($user->isAdmin() || $user->isOfficier()) {
            return $query->active();
        }

        // Sous-officier : filtre par site si spécifié
        if ($user->isSousOfficier() && $siteId) {
            return $query->active()->forSite($siteId);
        }

        return $query->active();
    }

    // ========== ACTIVITY LOG ==========

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nom', 'type', 'categorie', 'destinataire', 'site_id', 'statut', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

<?php

namespace App\Models;

use App\Enums\StatutPermanence;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Permanence extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'officier_id',
        'date',
        'heure_debut',
        'heure_fin',
        'statut',
        'commentaire_officier',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'heure_debut' => 'datetime:H:i',
            'heure_fin' => 'datetime:H:i',
            'statut' => StatutPermanence::class,
            'validated_at' => 'datetime',
        ];
    }

    // ========== RELATIONS ==========

    /**
     * Officier responsable de la permanence
     */
    public function officier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officier_id');
    }

    /**
     * Affectations des sous-officiers
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(PermanenceSousOfficier::class);
    }

    /**
     * Sous-officiers affectés à cette permanence
     */
    public function sousOfficiers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permanence_sous_officier', 'permanence_id', 'sous_officier_id')
            ->withPivot('site_id')
            ->withTimestamps();
    }

    /**
     * Relations managériales de cette permanence
     */
    public function relationsManageriales(): HasMany
    {
        return $this->hasMany(RelationManageriale::class)->orderBy('heure_evenement');
    }

    /**
     * Relevés d'énergie des appareils (sous-officiers)
     */
    public function relevesEnergie(): HasMany
    {
        return $this->hasMany(ReleveEnergie::class)->orderBy('heure_releve');
    }

    /**
     * Redémarrages d'appareils (officiers)
     */
    public function redemarragesAppareils(): HasMany
    {
        return $this->hasMany(RedemarrageAppareil::class)->orderBy('heure_debut');
    }

    /**
     * Réceptions du matériel
     */
    public function receptionMateriels(): HasMany
    {
        return $this->hasMany(ReceptionMateriel::class);
    }

    // ========== HELPERS ==========

    /**
     * Vérifie si la permanence est verrouillée (validée)
     */
    public function isLocked(): bool
    {
        return $this->statut === StatutPermanence::Validee;
    }

    /**
     * Vérifie si la permanence peut être modifiée
     */
    public function isEditable(): bool
    {
        return !$this->isLocked();
    }

    /**
     * Vérifie si un utilisateur peut saisir des événements
     */
    public function canUserAddEvents(User $user): bool
    {
        if ($this->isLocked()) {
            return false;
        }

        // Admin et officier responsable peuvent toujours
        if ($user->isAdmin() || ($user->isOfficier() && $this->officier_id === $user->id)) {
            return true;
        }

        // Sous-officier affecté peut saisir
        if ($user->isSousOfficier()) {
            return $this->sousOfficiers()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Valide la permanence
     */
    public function valider(): bool
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->update([
            'statut' => StatutPermanence::Validee,
            'validated_at' => now(),
        ]);

        return true;
    }

    /**
     * Passe la permanence en cours
     */
    public function demarrer(): bool
    {
        if ($this->statut !== StatutPermanence::Planifiee) {
            return false;
        }

        $this->update([
            'statut' => StatutPermanence::EnCours,
        ]);

        return true;
    }

    // ========== SCOPES ==========

    /**
     * Scope pour filtrer les permanences visibles par un utilisateur.
     * CLOISONNEMENT :
     * - Admin/Officier : toutes les permanences
     * - Sous-officier : uniquement celles où il est affecté
     */
    public function scopeForUser($query, User $user)
    {
        // Admin voit tout
        if ($user->isAdmin()) {
            return $query;
        }

        // Officier voit tout (pourrait être filtré si nécessaire)
        if ($user->isOfficier()) {
            return $query;
        }

        // Sous-officier : UNIQUEMENT les permanences où il est affecté
        if ($user->isSousOfficier()) {
            return $query->whereHas('sousOfficiers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        // Sécurité : aucun résultat par défaut
        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope pour filtrer les permanences que l'utilisateur peut imprimer en PDF.
     * Règles strictes : validée + (admin OU officier responsable)
     */
    public function scopePrintableBy($query, User $user)
    {
        // Sous-officier ne peut JAMAIS imprimer
        if ($user->isSousOfficier()) {
            return $query->whereRaw('1 = 0');
        }

        // Doit être validée
        $query->where('statut', StatutPermanence::Validee);

        // Admin peut imprimer toutes les validées
        if ($user->isAdmin()) {
            return $query;
        }

        // Officier : uniquement ses propres permanences
        if ($user->isOfficier()) {
            return $query->where('officier_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeValidees($query)
    {
        return $query->where('statut', StatutPermanence::Validee);
    }

    public function scopeNonValidees($query)
    {
        return $query->where('statut', '!=', StatutPermanence::Validee);
    }

    // ========== ACTIVITY LOG ==========

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['officier_id', 'date', 'heure_debut', 'heure_fin', 'statut', 'commentaire_officier', 'validated_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

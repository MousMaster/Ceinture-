<?php

namespace App\Models;

use App\Enums\UserType;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $fillable = [
        'nom',
        'prenom',
        'matricule',
        'email',
        'password',
        'type',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
            'is_active' => 'boolean',
        ];
    }

    // ========== FILAMENT ==========

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    // ========== ACCESSORS ==========

    public function getNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    // ========== RELATIONS ==========

    /**
     * Permanences où l'utilisateur est officier responsable
     */
    public function permanencesAsOfficier(): HasMany
    {
        return $this->hasMany(Permanence::class, 'officier_id');
    }

    /**
     * Affectations du sous-officier aux permanences
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(PermanenceSousOfficier::class, 'sous_officier_id');
    }

    /**
     * Permanences auxquelles le sous-officier est affecté
     */
    public function permanences(): BelongsToMany
    {
        return $this->belongsToMany(Permanence::class, 'permanence_sous_officier', 'sous_officier_id', 'permanence_id')
            ->withPivot('site_id')
            ->withTimestamps();
    }

    /**
     * Relations managériales saisies par ce sous-officier
     */
    public function relationsManageriales(): HasMany
    {
        return $this->hasMany(RelationManageriale::class, 'sous_officier_id');
    }

    // ========== HELPERS ==========

    public function isAdmin(): bool
    {
        return $this->type === UserType::Admin;
    }

    public function isOfficier(): bool
    {
        return $this->type === UserType::Officier;
    }

    public function isSousOfficier(): bool
    {
        return $this->type === UserType::SousOfficier;
    }

    public function canManagePermanence(Permanence $permanence): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isOfficier()) {
            return $permanence->officier_id === $this->id;
        }

        return false;
    }

    public function isAffectedToPermanence(Permanence $permanence): bool
    {
        return $this->affectations()
            ->where('permanence_id', $permanence->id)
            ->exists();
    }

    // ========== ACTIVITY LOG ==========

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nom', 'prenom', 'email', 'type', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

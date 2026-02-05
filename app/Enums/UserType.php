<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserType: string implements HasLabel, HasColor
{
    case Admin = 'admin';
    case Officier = 'officier';
    case SousOfficier = 'sous_officier';
    case Viewer = 'viewer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => __('users.types.admin'),
            self::Officier => __('users.types.officier'),
            self::SousOfficier => __('users.types.sous_officier'),
            self::Viewer => __('users.types.viewer'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Officier => 'warning',
            self::SousOfficier => 'success',
            self::Viewer => 'info',
        };
    }

    /**
     * Vérifie si ce type peut modifier des données.
     */
    public function canEdit(): bool
    {
        return match ($this) {
            self::Admin, self::Officier => true,
            self::SousOfficier => true, // Uniquement ses propres saisies
            self::Viewer => false,
        };
    }

    /**
     * Vérifie si ce type est en lecture seule.
     */
    public function isReadOnly(): bool
    {
        return $this === self::Viewer;
    }

    public static function forSelect(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }
}

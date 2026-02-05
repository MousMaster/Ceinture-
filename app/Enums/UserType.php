<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserType: string implements HasLabel, HasColor
{
    case Admin = 'admin';
    case Officier = 'officier';
    case SousOfficier = 'sous_officier';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Officier => 'Officier',
            self::SousOfficier => 'Sous-officier',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Officier => 'warning',
            self::SousOfficier => 'success',
        };
    }

    public static function forSelect(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }
}

<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Fonction du sous-officier.
 * Utilisé pour distinguer les opérateurs des chefs de poste dans le PDF.
 */
enum FonctionSousOfficier: string implements HasLabel, HasColor
{
    case Operateur = 'operateur';
    case ChefPoste = 'chef_poste';

    public function getLabel(): string
    {
        return match ($this) {
            self::Operateur => __('users.fonctions.operateur'),
            self::ChefPoste => __('users.fonctions.chef_poste'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Operateur => 'primary',
            self::ChefPoste => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Operateur => 'heroicon-o-user',
            self::ChefPoste => 'heroicon-o-user-group',
        };
    }

    public static function forSelect(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }
}

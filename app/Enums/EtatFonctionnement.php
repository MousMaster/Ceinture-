<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * État de fonctionnement d'un appareil lors de la réception.
 */
enum EtatFonctionnement: string implements HasLabel, HasColor
{
    case Fonctionne = 'fonctionne';
    case Endommage = 'endommage';
    case HorsService = 'hors_service';

    public function getLabel(): string
    {
        return match ($this) {
            self::Fonctionne => __('materiel.etats.fonctionne'),
            self::Endommage => __('materiel.etats.endommage'),
            self::HorsService => __('materiel.etats.hors_service'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Fonctionne => 'success',
            self::Endommage => 'warning',
            self::HorsService => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Fonctionne => 'heroicon-o-check-circle',
            self::Endommage => 'heroicon-o-exclamation-triangle',
            self::HorsService => 'heroicon-o-x-circle',
        };
    }

    public static function forSelect(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }
}

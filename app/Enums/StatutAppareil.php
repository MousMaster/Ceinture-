<?php

namespace App\Enums;

enum StatutAppareil: string
{
    case Actif = 'actif';
    case HorsService = 'hors_service';

    public function getLabel(): string
    {
        return match ($this) {
            self::Actif => __('appareil.statuts.actif'),
            self::HorsService => __('appareil.statuts.hors_service'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Actif => 'success',
            self::HorsService => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Actif => 'heroicon-o-check-circle',
            self::HorsService => 'heroicon-o-x-circle',
        };
    }

    /**
     * Retourne les options pour les selects Filament.
     */
    public static function forSelect(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }
}

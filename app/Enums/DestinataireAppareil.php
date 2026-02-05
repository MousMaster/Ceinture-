<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Destinataire de l'appareil.
 * Permet de filtrer les appareils selon le rôle du personnel.
 */
enum DestinataireAppareil: string implements HasLabel, HasColor
{
    case Officier = 'officier';
    case Operateur = 'operateur';

    public function getLabel(): string
    {
        return match ($this) {
            self::Officier => __('materiel.destinataires.officier'),
            self::Operateur => __('materiel.destinataires.operateur'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Officier => 'warning',
            self::Operateur => 'primary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Officier => 'heroicon-o-star',
            self::Operateur => 'heroicon-o-user',
        };
    }

    public static function forSelect(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }
}

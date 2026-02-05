<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StatutPermanence: string implements HasLabel, HasColor, HasIcon
{
    case Planifiee = 'planifiee';
    case EnCours = 'en_cours';
    case Validee = 'validee';

    public function getLabel(): string
    {
        return match ($this) {
            self::Planifiee => __('permanence.statuts.planifiee'),
            self::EnCours => __('permanence.statuts.en_cours'),
            self::Validee => __('permanence.statuts.validee'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Planifiee => 'gray',
            self::EnCours => 'warning',
            self::Validee => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Planifiee => 'heroicon-o-clock',
            self::EnCours => 'heroicon-o-play',
            self::Validee => 'heroicon-o-check-circle',
        };
    }

    public static function forSelect(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }

    public function isLocked(): bool
    {
        return $this === self::Validee;
    }
}

<?php

namespace App\Filament\Resources\AppareilResource\Pages;

use App\Filament\Resources\AppareilResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppareil extends EditRecord
{
    protected static string $resource = AppareilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->isAdmin()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

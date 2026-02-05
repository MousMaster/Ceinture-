<?php

namespace App\Filament\Resources\AppareilResource\Pages;

use App\Filament\Resources\AppareilResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppareil extends CreateRecord
{
    protected static string $resource = AppareilResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

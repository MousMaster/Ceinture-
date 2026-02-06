<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\Locked;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    #[Locked]
    public ?string $settingType = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Mémorise le type pour normalisation
        $this->settingType = $this->record->type;
    }

    public function hydrate(): void
    {
        // Intercepte les données corrompues lors de la réhydratation Livewire
        if ($this->settingType === 'file' && isset($this->data['value'])) {
            $value = $this->data['value'];
            if ($value === false || $value === 'false' || $value === null || $value === '') {
                $this->data['value'] = [];
            } elseif (is_string($value) && $value !== '') {
                $this->data['value'] = [$value];
            } elseif (!is_array($value)) {
                $this->data['value'] = [];
            }
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Normalise la valeur pour les types file AVANT de remplir le formulaire
        if (isset($data['type']) && $data['type'] === 'file') {
            $value = $data['value'] ?? null;

            // Convertit false/null/string vide en tableau vide pour FileUpload
            if ($value === false || $value === null || $value === '' || $value === 'false') {
                $data['value'] = [];
            } elseif (is_string($value) && $value !== '') {
                // Convertit une string en tableau pour FileUpload
                $data['value'] = [$value];
            } elseif (!is_array($value)) {
                $data['value'] = [];
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PermanenceResource\Pages;

use App\Enums\StatutPermanence;
use App\Filament\Resources\PermanenceResource;
use App\Services\PdfPermanenceService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPermanence extends ViewRecord
{
    protected static string $resource = PermanenceResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $record = $this->getRecord();

        return [
            Actions\Action::make('imprimer')
                ->label('Imprimer (PDF)')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('pdf.permanence.stream', $record))
                ->openUrlInNewTab()
                ->visible(fn () => 
                    $user->isAdmin() || $user->canManagePermanence($record)
                ),

            Actions\Action::make('telecharger_pdf')
                ->label('Télécharger PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn () => route('pdf.permanence.download', $record))
                ->visible(fn () => 
                    $record->isLocked() && 
                    ($user->isAdmin() || $user->canManagePermanence($record))
                ),

            Actions\EditAction::make()
                ->hidden(fn () => $record->isLocked() && !$user->isAdmin()),

            Actions\Action::make('demarrer')
                ->label('Démarrer')
                ->icon('heroicon-o-play')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Démarrer la permanence')
                ->modalDescription('Voulez-vous démarrer cette permanence ?')
                ->action(function () {
                    $this->getRecord()->demarrer();
                    Notification::make()
                        ->title('Permanence démarrée')
                        ->success()
                        ->send();
                    $this->refreshFormData(['statut']);
                })
                ->visible(fn () => 
                    $record->statut === StatutPermanence::Planifiee && 
                    $user->canManagePermanence($record)
                ),

            Actions\Action::make('valider')
                ->label('Valider')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Valider la permanence')
                ->modalDescription('Cette action est irréversible. Une fois validée, la permanence ne pourra plus être modifiée.')
                ->action(function () {
                    $this->getRecord()->valider();
                    Notification::make()
                        ->title('Permanence validée')
                        ->success()
                        ->send();
                    $this->refreshFormData(['statut', 'validated_at']);
                })
                ->visible(fn () => 
                    !$record->isLocked() && 
                    $user->canManagePermanence($record)
                ),

            Actions\Action::make('rouvrir')
                ->label('Rouvrir')
                ->icon('heroicon-o-lock-open')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rouvrir la permanence')
                ->modalDescription('Cette action permettra de modifier à nouveau la permanence.')
                ->action(function () {
                    $this->getRecord()->update([
                        'statut' => StatutPermanence::EnCours,
                        'validated_at' => null,
                    ]);
                    Notification::make()
                        ->title('Permanence rouverte')
                        ->warning()
                        ->send();
                    $this->refreshFormData(['statut', 'validated_at']);
                })
                ->visible(fn () => 
                    $record->isLocked() && 
                    $user->isAdmin()
                ),
        ];
    }
}

<?php

namespace App\Filament\Resources\PermanenceResource\Pages;

use App\Enums\StatutPermanence;
use App\Filament\Resources\PermanenceResource;
use App\Services\PdfPermanenceService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

/**
 * Page de vue d'une permanence avec cloisonnement strict.
 * 
 * CLOISONNEMENT :
 * - Sous-officier ne voit PAS les actions de gestion (démarrer, valider, rouvrir)
 * - Sous-officier ne voit PAS l'action d'impression PDF
 * - Sous-officier ne peut voir que les permanences où il est affecté
 */
class ViewPermanence extends ViewRecord
{
    protected static string $resource = PermanenceResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $record = $this->getRecord();

        return [
            // Action PDF avec règles STRICTES :
            // - Permanence DOIT être validée
            // - Sous-officier JAMAIS autorisé
            // - Seuls Admin et Officier responsable
            Actions\Action::make('imprimer')
                ->label(__('permanence.actions.print'))
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('pdf.permanence.stream', $record))
                ->openUrlInNewTab()
                ->visible(function () use ($user, $record) {
                    // Règle 1 : Permanence DOIT être validée
                    if (!$record->isLocked()) {
                        return false;
                    }
                    
                    // Règle 2 : Sous-officier JAMAIS autorisé
                    if ($user->isSousOfficier()) {
                        return false;
                    }
                    
                    // Règle 3 : Admin peut toujours imprimer
                    if ($user->isAdmin()) {
                        return true;
                    }
                    
                    // Règle 4 : Officier uniquement s'il est responsable
                    return $user->isOfficier() && $record->officier_id === $user->id;
                }),

            Actions\Action::make('telecharger_pdf')
                ->label(__('permanence.actions.download_pdf'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn () => route('pdf.permanence.download', $record))
                ->visible(function () use ($user, $record) {
                    // Mêmes règles que pour imprimer
                    if (!$record->isLocked()) {
                        return false;
                    }
                    if ($user->isSousOfficier()) {
                        return false;
                    }
                    if ($user->isAdmin()) {
                        return true;
                    }
                    return $user->isOfficier() && $record->officier_id === $user->id;
                }),

            Actions\EditAction::make()
                ->hidden(function () use ($user, $record) {
                    // Sous-officier ne peut jamais éditer une permanence
                    if ($user->isSousOfficier()) {
                        return true;
                    }
                    return $record->isLocked() && !$user->isAdmin();
                }),

            // Actions de gestion : JAMAIS visibles pour sous-officier
            Actions\Action::make('demarrer')
                ->label(__('permanence.actions.start'))
                ->icon('heroicon-o-play')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading(__('permanence.modals.start_title'))
                ->modalDescription(__('permanence.messages.start_confirm'))
                ->action(function () {
                    $this->getRecord()->demarrer();
                    Notification::make()
                        ->title(__('permanence.messages.started'))
                        ->success()
                        ->send();
                    $this->refreshFormData(['statut']);
                })
                ->visible(function () use ($user, $record) {
                    // Sous-officier JAMAIS autorisé à démarrer
                    if ($user->isSousOfficier()) {
                        return false;
                    }
                    return $record->statut === StatutPermanence::Planifiee && 
                           $user->canManagePermanence($record);
                }),

            Actions\Action::make('valider')
                ->label(__('permanence.actions.validate'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('permanence.modals.validate_title'))
                ->modalDescription(__('permanence.messages.locked_warning'))
                ->action(function () {
                    $this->getRecord()->valider();
                    Notification::make()
                        ->title(__('permanence.messages.validated'))
                        ->success()
                        ->send();
                    $this->refreshFormData(['statut', 'validated_at']);
                })
                ->visible(function () use ($user, $record) {
                    // Sous-officier JAMAIS autorisé à valider
                    if ($user->isSousOfficier()) {
                        return false;
                    }
                    return !$record->isLocked() && 
                           $user->canManagePermanence($record);
                }),

            Actions\Action::make('rouvrir')
                ->label(__('permanence.actions.reopen'))
                ->icon('heroicon-o-lock-open')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('permanence.modals.reopen_title'))
                ->modalDescription(__('permanence.messages.reopen_confirm'))
                ->action(function () {
                    $this->getRecord()->update([
                        'statut' => StatutPermanence::EnCours,
                        'validated_at' => null,
                    ]);
                    Notification::make()
                        ->title(__('permanence.messages.reopened'))
                        ->warning()
                        ->send();
                    $this->refreshFormData(['statut', 'validated_at']);
                })
                ->visible(fn () => 
                    $record->isLocked() && 
                    $user->isAdmin() // Seul l'admin peut rouvrir
                ),
        ];
    }
}

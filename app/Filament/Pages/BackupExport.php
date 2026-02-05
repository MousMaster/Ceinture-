<?php

namespace App\Filament\Pages;

use App\Services\ExportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Page de Sauvegarde & Export - RÉSERVÉE EXCLUSIVEMENT À L'ADMINISTRATEUR.
 * 
 * Cette page permet d'exporter les données du système vers différents formats.
 * Aucun autre rôle ne peut accéder à cette page.
 */
class BackupExport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string $view = 'filament.pages.backup-export';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 99;

    public ?string $exportType = 'permanences';
    public ?string $exportFormat = 'csv';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    /**
     * SÉCURITÉ : Seul l'administrateur peut accéder à cette page.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getNavigationLabel(): string
    {
        return __('common.navigation.settings') . ' - Export';
    }

    public function getTitle(): string
    {
        return 'Sauvegarde & Export';
    }

    public function getSubheading(): string
    {
        return 'Export des données du système - Réservé à l\'administrateur';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Options d\'export')
                    ->description('Sélectionnez les données à exporter et le format souhaité.')
                    ->schema([
                        Select::make('exportType')
                            ->label('Type de données')
                            ->options([
                                'users' => 'Utilisateurs',
                                'permanences' => 'Permanences',
                                'events' => 'Événements (Relations managériales)',
                                'settings' => 'Paramètres système',
                                'audit' => 'Journaux d\'audit',
                                'full' => 'Sauvegarde complète (JSON)',
                            ])
                            ->default('permanences')
                            ->required(),
                        Select::make('exportFormat')
                            ->label('Format')
                            ->options([
                                'csv' => 'CSV (Excel compatible)',
                                'json' => 'JSON',
                            ])
                            ->default('csv')
                            ->required()
                            ->visible(fn ($get) => $get('exportType') !== 'full'),
                        DatePicker::make('dateFrom')
                            ->label('Date de début')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->visible(fn ($get) => in_array($get('exportType'), ['permanences', 'events', 'audit'])),
                        DatePicker::make('dateTo')
                            ->label('Date de fin')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->visible(fn ($get) => in_array($get('exportType'), ['permanences', 'events', 'audit'])),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    return $this->processExport();
                }),
            Action::make('fullBackup')
                ->label('Sauvegarde complète')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sauvegarde complète')
                ->modalDescription('Cette action va générer une sauvegarde complète de toutes les données au format JSON.')
                ->action(function () {
                    return $this->processFullBackup();
                }),
        ];
    }

    /**
     * Traite l'export selon les options sélectionnées.
     */
    protected function processExport(): StreamedResponse
    {
        try {
            $exportService = new ExportService();
            $filters = [];

            if ($this->dateFrom) {
                $filters['date_from'] = $this->dateFrom;
            }
            if ($this->dateTo) {
                $filters['date_to'] = $this->dateTo;
            }

            $result = match ($this->exportType) {
                'users' => $exportService->exportUsers($this->exportFormat),
                'permanences' => $exportService->exportPermanences($this->exportFormat, $filters),
                'events' => $exportService->exportRelationsManageriales($this->exportFormat),
                'settings' => $exportService->exportSettings($this->exportFormat),
                'audit' => $exportService->exportAuditLogs($this->exportFormat, $filters),
                'full' => $exportService->exportFullBackup(),
                default => throw new \Exception('Type d\'export non reconnu'),
            };

            Notification::make()
                ->title('Export réussi')
                ->body("Fichier {$result['filename']} généré avec succès.")
                ->success()
                ->send();

            return Response::streamDownload(
                fn () => print($result['content']),
                $result['filename'],
                ['Content-Type' => $result['mime']]
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur d\'export')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return Response::streamDownload(
                fn () => print('Erreur: ' . $e->getMessage()),
                'erreur.txt',
                ['Content-Type' => 'text/plain']
            );
        }
    }

    /**
     * Traite la sauvegarde complète.
     */
    protected function processFullBackup(): StreamedResponse
    {
        try {
            $exportService = new ExportService();
            $result = $exportService->exportFullBackup();

            Notification::make()
                ->title('Sauvegarde réussie')
                ->body("Fichier {$result['filename']} généré avec succès.")
                ->success()
                ->send();

            return Response::streamDownload(
                fn () => print($result['content']),
                $result['filename'],
                ['Content-Type' => $result['mime']]
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de sauvegarde')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return Response::streamDownload(
                fn () => print('Erreur: ' . $e->getMessage()),
                'erreur.txt',
                ['Content-Type' => 'text/plain']
            );
        }
    }
}

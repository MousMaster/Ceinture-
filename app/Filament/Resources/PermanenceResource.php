<?php

namespace App\Filament\Resources;

use App\Enums\StatutPermanence;
use App\Enums\UserType;
use App\Filament\Resources\PermanenceResource\Pages;
use App\Filament\Resources\PermanenceResource\RelationManagers;
use App\Models\Permanence;
use App\Models\Site;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PermanenceResource extends Resource
{
    protected static ?string $model = Permanence::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('permanence.resource.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('permanence.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('permanence.resource.plural');
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();

        return $form
            ->schema([
                Forms\Components\Section::make(__('permanence.sections.info'))
                    ->schema([
                        Forms\Components\Select::make('officier_id')
                            ->label(__('permanence.fields.officier_id'))
                            ->options(function () use ($user) {
                                $query = User::where('type', UserType::Officier)->where('is_active', true);
                                return $query->get()->pluck('nom_complet', 'id');
                            })
                            ->default(fn () => $user->isOfficier() ? $user->id : null)
                            ->disabled(fn () => !$user->isAdmin())
                            ->dehydrated(true)
                            ->required()
                            ->searchable(),
                        Forms\Components\DatePicker::make('date')
                            ->label(__('permanence.fields.date'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TimePicker::make('heure_debut')
                            ->label(__('permanence.fields.heure_debut'))
                            ->required()
                            ->seconds(false)
                            ->native(false),
                        Forms\Components\TimePicker::make('heure_fin')
                            ->label(__('permanence.fields.heure_fin'))
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->after('heure_debut'),
                    ])->columns(2),

                // Section affectations : MASQUÉE pour sous-officiers
                Forms\Components\Section::make(__('permanence.sections.affectations'))
                    ->schema([
                        Forms\Components\Repeater::make('affectations')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('sous_officier_id')
                                    ->label(__('users.types.sous_officier'))
                                    ->options(
                                        User::where('type', UserType::SousOfficier)
                                            ->where('is_active', true)
                                            ->get()
                                            ->pluck('nom_complet', 'id')
                                    )
                                    ->required()
                                    ->searchable(),
                                Forms\Components\Select::make('site_id')
                                    ->label('Site')
                                    ->options(Site::where('is_active', true)->pluck('nom', 'id'))
                                    ->required()
                                    ->searchable(),
                            ])
                            ->columns(2)
                            ->addActionLabel(__('permanence.actions.add_affectation'))
                            ->reorderable(false)
                            ->defaultItems(0)
                            ->disabled(fn (?Permanence $record) => $record?->isLocked() && !auth()->user()->isAdmin()),
                    ])
                    ->hidden(function (string $operation) use ($user): bool {
                        // Masqué à la création ET pour les sous-officiers
                        return $operation === 'create' || $user->isSousOfficier();
                    }),

                // Section commentaire officier : MASQUÉE pour sous-officiers
                Forms\Components\Section::make(__('permanence.sections.commentaire'))
                    ->schema([
                        Forms\Components\Textarea::make('commentaire_officier')
                            ->label(__('permanence.fields.commentaire_officier'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn (?Permanence $record) => $record?->isLocked() && !auth()->user()->isAdmin()),
                    ])
                    ->hidden(fn () => $user->isSousOfficier()),

                // Section validation : MASQUÉE pour sous-officiers
                Forms\Components\Section::make(__('permanence.sections.statut'))
                    ->schema([
                        Forms\Components\Select::make('statut')
                            ->label(__('permanence.fields.statut'))
                            ->options(StatutPermanence::forSelect())
                            ->default(StatutPermanence::Planifiee->value)
                            ->disabled(fn () => !$user->isAdmin())
                            ->dehydrated(true)
                            ->required(),
                        Forms\Components\Placeholder::make('validated_at')
                            ->label(__('permanence.fields.validated_at'))
                            ->content(fn (?Permanence $record) => $record?->validated_at?->format('d/m/Y H:i') ?? '-')
                            ->hidden(fn (?Permanence $record) => !$record?->isLocked()),
                    ])
                    ->columns(2)
                    ->hidden(fn () => $user->isSousOfficier()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label(__('common.dates.date'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('officier.nom_complet')
                    ->label(__('permanence.fields.officier'))
                    ->searchable(['nom', 'prenom'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('heure_debut')
                    ->label(__('permanence.fields.heure_debut'))
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('heure_fin')
                    ->label(__('permanence.fields.heure_fin'))
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('statut')
                    ->label(__('permanence.fields.statut'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('affectations_count')
                    ->label(__('users.types.sous_officier'))
                    ->counts('affectations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('relations_manageriales_count')
                    ->label(__('permanence.relation.plural'))
                    ->counts('relationsManageriales')
                    ->sortable(),
                Tables\Columns\TextColumn::make('validated_at')
                    ->label(__('permanence.fields.validated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options(StatutPermanence::forSelect()),
                // Filtre officier : masqué pour sous-officiers
                Tables\Filters\SelectFilter::make('officier_id')
                    ->label(__('permanence.fields.officier'))
                    ->relationship('officier', 'nom')
                    ->searchable()
                    ->preload()
                    ->hidden(fn () => auth()->user()->isSousOfficier()),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('common.dates.from')),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('common.dates.to')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Edition : sous-officier JAMAIS autorisé
                Tables\Actions\EditAction::make()
                    ->hidden(function (Permanence $record) {
                        $user = auth()->user();
                        // Sous-officier ne peut jamais éditer
                        if ($user->isSousOfficier()) {
                            return true;
                        }
                        return $record->isLocked() && !$user->isAdmin();
                    }),
                // Actions de gestion : sous-officier JAMAIS autorisé
                Tables\Actions\Action::make('demarrer')
                    ->label(__('permanence.actions.start'))
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('permanence.modals.start_title'))
                    ->modalDescription(__('permanence.messages.start_confirm'))
                    ->action(function (Permanence $record) {
                        $record->demarrer();
                        Notification::make()
                            ->title(__('permanence.messages.started'))
                            ->success()
                            ->send();
                    })
                    ->visible(function (Permanence $record) {
                        $user = auth()->user();
                        // Sous-officier JAMAIS autorisé
                        if ($user->isSousOfficier()) {
                            return false;
                        }
                        return $record->statut === StatutPermanence::Planifiee && 
                               $user->canManagePermanence($record);
                    }),
                Tables\Actions\Action::make('valider')
                    ->label(__('permanence.actions.validate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('permanence.modals.validate_title'))
                    ->modalDescription(__('permanence.messages.locked_warning'))
                    ->action(function (Permanence $record) {
                        $record->valider();
                        Notification::make()
                            ->title(__('permanence.messages.validated'))
                            ->success()
                            ->send();
                    })
                    ->visible(function (Permanence $record) {
                        $user = auth()->user();
                        // Sous-officier JAMAIS autorisé
                        if ($user->isSousOfficier()) {
                            return false;
                        }
                        return !$record->isLocked() && 
                               $user->canManagePermanence($record);
                    }),
                Tables\Actions\Action::make('rouvrir')
                    ->label(__('permanence.actions.reopen'))
                    ->icon('heroicon-o-lock-open')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('permanence.modals.reopen_title'))
                    ->modalDescription(__('permanence.messages.reopen_confirm'))
                    ->action(function (Permanence $record) {
                        $record->update([
                            'statut' => StatutPermanence::EnCours,
                            'validated_at' => null,
                        ]);
                        Notification::make()
                            ->title(__('permanence.messages.reopened'))
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Permanence $record) => 
                        $record->isLocked() && 
                        auth()->user()->isAdmin()
                    ),
                // Action PDF avec règles STRICTES :
                // - Permanence DOIT être validée
                // - Sous-officier JAMAIS autorisé
                // - Seuls Admin et Officier responsable
                Tables\Actions\Action::make('imprimer')
                    ->label(__('permanence.actions.print'))
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Permanence $record) => route('pdf.permanence.stream', $record))
                    ->openUrlInNewTab()
                    ->visible(function (Permanence $record) {
                        $user = auth()->user();
                        
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RelationsManagerialesRelationManager::class,
            RelationManagers\RelevesEnergieRelationManager::class,
            RelationManagers\RedemarragesAppareilsRelationManager::class,
            RelationManagers\ReceptionMaterielsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermanences::route('/'),
            'create' => Pages\CreatePermanence::route('/create'),
            'view' => Pages\ViewPermanence::route('/{record}'),
            'edit' => Pages\EditPermanence::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        // Sous-officier ne voit que les permanences auxquelles il est affecté
        if ($user->isSousOfficier()) {
            $query->whereHas('sousOfficiers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return $query;
    }
}

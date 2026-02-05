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

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?string $modelLabel = 'Permanence';

    protected static ?string $pluralModelLabel = 'Permanences';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $user = auth()->user();

        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la permanence')
                    ->schema([
                        Forms\Components\Select::make('officier_id')
                            ->label('Officier responsable')
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
                            ->label('Date de la permanence')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TimePicker::make('heure_debut')
                            ->label('Heure de début')
                            ->required()
                            ->seconds(false)
                            ->native(false),
                        Forms\Components\TimePicker::make('heure_fin')
                            ->label('Heure de fin')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->after('heure_debut'),
                    ])->columns(2),

                Forms\Components\Section::make('Affectations des sous-officiers')
                    ->schema([
                        Forms\Components\Repeater::make('affectations')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('sous_officier_id')
                                    ->label('Sous-officier')
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
                            ->addActionLabel('Ajouter un sous-officier')
                            ->reorderable(false)
                            ->defaultItems(0)
                            ->disabled(fn (?Permanence $record) => $record?->isLocked() && !auth()->user()->isAdmin()),
                    ])
                    ->hidden(fn (string $operation): bool => $operation === 'create'),

                Forms\Components\Section::make('Commentaire')
                    ->schema([
                        Forms\Components\Textarea::make('commentaire_officier')
                            ->label('Commentaire de l\'officier')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn (?Permanence $record) => $record?->isLocked() && !auth()->user()->isAdmin()),
                    ]),

                Forms\Components\Section::make('Statut')
                    ->schema([
                        Forms\Components\Select::make('statut')
                            ->label('Statut')
                            ->options(StatutPermanence::forSelect())
                            ->default(StatutPermanence::Planifiee->value)
                            ->disabled(fn () => !$user->isAdmin())
                            ->dehydrated(true)
                            ->required(),
                        Forms\Components\Placeholder::make('validated_at')
                            ->label('Validée le')
                            ->content(fn (?Permanence $record) => $record?->validated_at?->format('d/m/Y H:i') ?? '-')
                            ->hidden(fn (?Permanence $record) => !$record?->isLocked()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('officier.nom_complet')
                    ->label('Officier')
                    ->searchable(['nom', 'prenom'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('heure_debut')
                    ->label('Début')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('heure_fin')
                    ->label('Fin')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('affectations_count')
                    ->label('Sous-officiers')
                    ->counts('affectations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('relations_manageriales_count')
                    ->label('Événements')
                    ->counts('relationsManageriales')
                    ->sortable(),
                Tables\Columns\TextColumn::make('validated_at')
                    ->label('Validée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options(StatutPermanence::forSelect()),
                Tables\Filters\SelectFilter::make('officier_id')
                    ->label('Officier')
                    ->relationship('officier', 'nom')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Au'),
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
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Permanence $record) => $record->isLocked() && !auth()->user()->isAdmin()),
                Tables\Actions\Action::make('demarrer')
                    ->label('Démarrer')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Démarrer la permanence')
                    ->modalDescription('Voulez-vous démarrer cette permanence ?')
                    ->action(function (Permanence $record) {
                        $record->demarrer();
                        Notification::make()
                            ->title('Permanence démarrée')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Permanence $record) => 
                        $record->statut === StatutPermanence::Planifiee && 
                        auth()->user()->canManagePermanence($record)
                    ),
                Tables\Actions\Action::make('valider')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Valider la permanence')
                    ->modalDescription('Cette action est irréversible. Une fois validée, la permanence ne pourra plus être modifiée.')
                    ->action(function (Permanence $record) {
                        $record->valider();
                        Notification::make()
                            ->title('Permanence validée')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Permanence $record) => 
                        !$record->isLocked() && 
                        auth()->user()->canManagePermanence($record)
                    ),
                Tables\Actions\Action::make('rouvrir')
                    ->label('Rouvrir')
                    ->icon('heroicon-o-lock-open')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rouvrir la permanence')
                    ->modalDescription('Cette action permettra de modifier à nouveau la permanence.')
                    ->action(function (Permanence $record) {
                        $record->update([
                            'statut' => StatutPermanence::EnCours,
                            'validated_at' => null,
                        ]);
                        Notification::make()
                            ->title('Permanence rouverte')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Permanence $record) => 
                        $record->isLocked() && 
                        auth()->user()->isAdmin()
                    ),
                Tables\Actions\Action::make('imprimer')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Permanence $record) => route('pdf.permanence.stream', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Permanence $record) => 
                        auth()->user()->isAdmin() || auth()->user()->canManagePermanence($record)
                    ),
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

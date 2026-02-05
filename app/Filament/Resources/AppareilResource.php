<?php

namespace App\Filament\Resources;

use App\Enums\DestinataireAppareil;
use App\Enums\StatutAppareil;
use App\Filament\Resources\AppareilResource\Pages;
use App\Models\Appareil;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resource Filament pour la gestion des appareils.
 * Paramétrable par admin et officier.
 * Liste dynamique utilisée dans les Relation Managers.
 */
class AppareilResource extends Resource
{
    protected static ?string $model = Appareil::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('appareil.resource.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('appareil.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('appareil.resource.plural');
    }

    /**
     * Visibilité : admin et officier uniquement pour la gestion.
     * Sous-officier ne voit pas ce menu.
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isOfficier());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('appareil.sections.info'))
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label(__('appareil.fields.nom'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('type')
                            ->label(__('appareil.fields.type'))
                            ->maxLength(100),
                        Forms\Components\TextInput::make('categorie')
                            ->label(__('appareil.fields.categorie'))
                            ->maxLength(100),
                        Forms\Components\TextInput::make('numero_serie')
                            ->label(__('appareil.fields.numero_serie'))
                            ->maxLength(100),
                        Forms\Components\Select::make('destinataire')
                            ->label(__('appareil.fields.destinataire'))
                            ->options(DestinataireAppareil::forSelect())
                            ->placeholder(__('appareil.placeholders.select_destinataire'))
                            ->helperText(__('appareil.helpers.destinataire')),
                    ])->columns(2),

                Forms\Components\Section::make(__('appareil.sections.localisation'))
                    ->schema([
                        Forms\Components\Select::make('site_id')
                            ->label(__('appareil.fields.site_id'))
                            ->options(Site::active()->pluck('nom', 'id'))
                            ->searchable()
                            ->placeholder(__('appareil.placeholders.select_site')),
                        Forms\Components\Select::make('statut')
                            ->label(__('appareil.fields.statut'))
                            ->options(StatutAppareil::forSelect())
                            ->default(StatutAppareil::Actif->value)
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('appareil.fields.is_active'))
                            ->default(true),
                    ])->columns(3),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label(__('appareil.fields.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label(__('appareil.fields.nom'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('appareil.fields.type'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categorie')
                    ->label(__('appareil.fields.categorie'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('destinataire')
                    ->label(__('appareil.fields.destinataire'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site.nom')
                    ->label(__('appareil.fields.site'))
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label(__('appareil.fields.statut'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('appareil.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.dates.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nom')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options(StatutAppareil::forSelect()),
                Tables\Filters\SelectFilter::make('destinataire')
                    ->label(__('appareil.fields.destinataire'))
                    ->options(DestinataireAppareil::forSelect()),
                Tables\Filters\SelectFilter::make('site_id')
                    ->label(__('appareil.fields.site'))
                    ->relationship('site', 'nom')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('appareil.fields.is_active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->isAdmin()),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppareils::route('/'),
            'create' => Pages\CreateAppareil::route('/create'),
            'edit' => Pages\EditAppareil::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('site');
    }
}

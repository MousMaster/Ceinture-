<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('settings.resource.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('settings.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('settings.resource.plural');
    }

    public static function canCreate(): bool
    {
        return false; // Pas de création manuelle
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('settings.sections.parameter'))
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label(__('settings.fields.label'))
                            ->disabled(),
                        Forms\Components\TextInput::make('key')
                            ->label(__('settings.fields.key'))
                            ->disabled(),
                        Forms\Components\Select::make('group')
                            ->label(__('settings.fields.group'))
                            ->options([
                                'institution' => __('settings.groups.institution'),
                                'logos' => __('settings.groups.logos'),
                                'pdf' => __('settings.groups.pdf'),
                                'general' => __('settings.groups.general'),
                            ])
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make(__('settings.sections.value'))
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label(__('settings.fields.value'))
                            ->maxLength(500)
                            ->visible(fn (Get $get) => $get('type') === 'string'),
                        Forms\Components\Textarea::make('value')
                            ->label(__('settings.fields.value'))
                            ->rows(4)
                            ->visible(fn (Get $get) => $get('type') === 'text'),
                        Forms\Components\FileUpload::make('value')
                            ->label(__('settings.fields.file'))
                            ->image()
                            ->directory('logos')
                            ->visibility('public')
                            ->imagePreviewHeight('150')
                            ->visible(fn (Get $get) => $get('type') === 'file')
                            ->afterStateHydrated(function (Forms\Components\FileUpload $component, $state) {
                                // Normalise la valeur pour éviter TypeError si false/null/non-string
                                if ($state === false || $state === null || $state === '') {
                                    $component->state(null);
                                } elseif (is_string($state)) {
                                    // Vérifie que le fichier existe avant de l'afficher
                                    $component->state($state);
                                } elseif (!is_array($state)) {
                                    // Pour tout autre type inattendu, reset à null
                                    $component->state(null);
                                }
                            }),
                        Forms\Components\Toggle::make('value')
                            ->label(__('settings.fields.value'))
                            ->visible(fn (Get $get) => $get('type') === 'boolean'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->label(__('settings.fields.group'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'institution' => 'primary',
                        'logos' => 'success',
                        'pdf' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->label(__('settings.fields.label'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->label(__('settings.fields.key'))
                    ->searchable()
                    ->fontFamily('mono')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('settings.fields.type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('settings.fields.value'))
                    ->limit(50)
                    ->placeholder('-'),
            ])
            ->defaultSort('group')
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label(__('settings.fields.group'))
                    ->options([
                        'institution' => __('settings.groups.institution'),
                        'logos' => __('settings.groups.logos'),
                        'pdf' => __('settings.groups.pdf'),
                        'general' => __('settings.groups.general'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }
}

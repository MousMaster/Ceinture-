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

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $modelLabel = 'Paramètre';

    protected static ?string $pluralModelLabel = 'Paramètres';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false; // Pas de création manuelle
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Paramètre')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label('Libellé')
                            ->disabled(),
                        Forms\Components\TextInput::make('key')
                            ->label('Clé')
                            ->disabled(),
                        Forms\Components\Select::make('group')
                            ->label('Groupe')
                            ->options([
                                'institution' => 'Institution',
                                'logos' => 'Logos',
                                'pdf' => 'PDF',
                                'general' => 'Général',
                            ])
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Valeur')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Valeur')
                            ->maxLength(500)
                            ->visible(fn (Get $get) => $get('type') === 'string'),
                        Forms\Components\Textarea::make('value')
                            ->label('Valeur')
                            ->rows(4)
                            ->visible(fn (Get $get) => $get('type') === 'text'),
                        Forms\Components\FileUpload::make('value')
                            ->label('Fichier')
                            ->image()
                            ->directory('logos')
                            ->visibility('public')
                            ->imagePreviewHeight('150')
                            ->visible(fn (Get $get) => $get('type') === 'file'),
                        Forms\Components\Toggle::make('value')
                            ->label('Valeur')
                            ->visible(fn (Get $get) => $get('type') === 'boolean'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->label('Groupe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'institution' => 'primary',
                        'logos' => 'success',
                        'pdf' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->label('Libellé')
                    ->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('Clé')
                    ->searchable()
                    ->fontFamily('mono')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->limit(50)
                    ->placeholder('-'),
            ])
            ->defaultSort('group')
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('Groupe')
                    ->options([
                        'institution' => 'Institution',
                        'logos' => 'Logos',
                        'pdf' => 'PDF',
                        'general' => 'Général',
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

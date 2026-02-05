<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('site.resource.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('site.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.resource.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('site.sections.info'))
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label(__('site.fields.nom'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label(__('site.fields.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText(__('site.messages.code_help')),
                        Forms\Components\TextInput::make('localisation')
                            ->label(__('site.fields.localisation'))
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('site.fields.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('site.fields.is_active'))
                            ->default(true)
                            ->helperText(__('site.messages.deactivate_help')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('site.fields.code'))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nom')
                    ->label(__('site.fields.nom'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('localisation')
                    ->label(__('site.fields.localisation'))
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('site.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('affectations_count')
                    ->label(__('site.fields.affectations_count'))
                    ->counts('affectations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('site.fields.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nom')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('common.status.active'))
                    ->trueLabel(__('common.status.active'))
                    ->falseLabel(__('common.status.inactive')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Enums\UserType;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('users.resource.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('users.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('users.resource.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('users.sections.personal'))
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label(__('users.fields.nom'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('prenom')
                            ->label(__('users.fields.prenom'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('matricule')
                            ->label(__('users.fields.matricule'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                    ])->columns(3),

                Forms\Components\Section::make(__('users.sections.access'))
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label(__('users.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label(__('users.fields.password'))
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label(__('users.fields.type'))
                            ->options(UserType::forSelect())
                            ->required()
                            ->native(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('users.fields.is_active'))
                            ->default(true)
                            ->helperText(__('users.messages.deactivate_help')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label(__('users.fields.nom_complet'))
                    ->searchable(['nom', 'prenom'])
                    ->sortable(['nom']),
                Tables\Columns\TextColumn::make('matricule')
                    ->label(__('users.fields.matricule'))
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('users.fields.email'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('users.fields.type'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('users.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('users.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nom')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(UserType::forSelect()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('common.status.active'))
                    ->trueLabel(__('common.status.active'))
                    ->falseLabel(__('common.status.inactive')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (User $record) => $record->is_active ? __('users.actions.deactivate') : __('users.actions.activate'))
                    ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['is_active' => !$record->is_active]))
                    ->hidden(fn (User $record) => $record->id === auth()->id()),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

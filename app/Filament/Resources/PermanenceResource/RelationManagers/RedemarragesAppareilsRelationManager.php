<?php

namespace App\Filament\Resources\PermanenceResource\RelationManagers;

use App\Models\Appareil;
use App\Models\RedemarrageAppareil;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Relation Manager pour les redémarrages d'appareils.
 * 
 * CLOISONNEMENT STRICT :
 * - INVISIBLE pour sous-officier (totalement masqué)
 * - Visible uniquement pour officier responsable et admin
 * - Actions désactivées si permanence validée
 */
class RedemarragesAppareilsRelationManager extends RelationManager
{
    protected static string $relationship = 'redemarragesAppareils';

    protected static ?string $recordTitleAttribute = 'appareil.nom';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('appareil.redemarrage.title');
    }

    /**
     * VISIBILITÉ STRICTE : INVISIBLE pour sous-officier.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();
        
        // Sous-officier : JAMAIS visible
        if ($user->isSousOfficier()) {
            return false;
        }
        
        // Admin voit toujours
        if ($user->isAdmin()) {
            return true;
        }
        
        // Officier : uniquement s'il est responsable
        if ($user->isOfficier()) {
            return $ownerRecord->officier_id === $user->id;
        }
        
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('appareil_id')
                    ->label(__('appareil.fields.nom'))
                    ->options(
                        Appareil::query()
                            ->active()
                            ->pluck('nom', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\TextInput::make('nombre_redemarrages')
                    ->label(__('appareil.redemarrage.nombre'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1),
                    
                Forms\Components\TextInput::make('motif')
                    ->label(__('appareil.redemarrage.motif'))
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TimePicker::make('heure_debut')
                    ->label(__('appareil.redemarrage.heure_debut'))
                    ->required()
                    ->seconds(false)
                    ->native(false)
                    ->default(now()->format('H:i')),
                    
                Forms\Components\TimePicker::make('heure_fin')
                    ->label(__('appareil.redemarrage.heure_fin'))
                    ->seconds(false)
                    ->native(false),
                    
                Forms\Components\Textarea::make('decision_officier')
                    ->label(__('appareil.redemarrage.decision'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $permanence = $this->getOwnerRecord();
        $isLocked = $permanence->isLocked();
        
        // Peut créer si pas verrouillé et (admin ou officier responsable)
        $canCreate = !$isLocked && ($user->isAdmin() || ($user->isOfficier() && $permanence->officier_id === $user->id));

        return $table
            ->recordTitleAttribute('appareil.nom')
            // CLOISONNEMENT : Filtrer par utilisateur (sous-officier n'aura jamais accès)
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                return $query->visibleBy($user);
            })
            ->columns([
                Tables\Columns\TextColumn::make('appareil.nom')
                    ->label(__('appareil.fields.nom'))
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('nombre_redemarrages')
                    ->label(__('appareil.redemarrage.nombre'))
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('motif')
                    ->label(__('appareil.redemarrage.motif'))
                    ->limit(40)
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('heure_debut')
                    ->label(__('appareil.redemarrage.heure_debut'))
                    ->time('H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('heure_fin')
                    ->label(__('appareil.redemarrage.heure_fin'))
                    ->time('H:i')
                    ->placeholder('-'),
                    
                Tables\Columns\TextColumn::make('officier.nom_complet')
                    ->label(__('appareil.redemarrage.auteur'))
                    ->sortable()
                    ->visible(fn () => $user->isAdmin()),
                    
                Tables\Columns\TextColumn::make('decision_officier')
                    ->label(__('appareil.redemarrage.decision'))
                    ->limit(30)
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.dates.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('heure_debut', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('appareil_id')
                    ->label(__('appareil.fields.nom'))
                    ->relationship('appareil', 'nom')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('appareil.redemarrage.add'))
                    ->visible($canCreate)
                    ->mutateFormDataUsing(function (array $data) use ($user): array {
                        $data['officier_id'] = $user->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (RedemarrageAppareil $record) => $record->canBeEditedBy($user)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (RedemarrageAppareil $record) => $record->canBeDeletedBy($user)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $user->isAdmin()),
                ]),
            ])
            ->emptyStateHeading(__('appareil.redemarrage.no_redemarrage'))
            ->emptyStateDescription(null);
    }
}

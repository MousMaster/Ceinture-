<?php

namespace App\Filament\Resources\PermanenceResource\RelationManagers;

use App\Models\Appareil;
use App\Models\ReleveEnergie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Relation Manager pour les relevés d'énergie.
 * 
 * CLOISONNEMENT STRICT :
 * - Visible pour sous-officier (ses propres relevés uniquement)
 * - Visible pour officier/admin (lecture seule après validation)
 * - Actions désactivées si permanence validée
 */
class RelevesEnergieRelationManager extends RelationManager
{
    protected static string $relationship = 'relevesEnergie';

    protected static ?string $recordTitleAttribute = 'appareil.nom';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('appareil.energie.title');
    }

    /**
     * VISIBILITÉ : Tous sauf si pas de sous-officier affecté.
     * Le cloisonnement est géré dans modifyQueryUsing().
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();
        
        // Admin et officier voient toujours
        if ($user->isAdmin() || $user->isOfficier()) {
            return true;
        }
        
        // Sous-officier : visible seulement s'il est affecté
        if ($user->isSousOfficier()) {
            return $ownerRecord->sousOfficiers()->where('users.id', $user->id)->exists();
        }
        
        return false;
    }

    public function form(Form $form): Form
    {
        $user = auth()->user();
        $permanence = $this->getOwnerRecord();
        
        // Récupérer le site du sous-officier si applicable
        $siteId = null;
        if ($user->isSousOfficier()) {
            $affectation = $permanence->affectations()
                ->where('sous_officier_id', $user->id)
                ->first();
            $siteId = $affectation?->site_id;
        }

        return $form
            ->schema([
                Forms\Components\Select::make('appareil_id')
                    ->label(__('appareil.fields.nom'))
                    ->options(function () use ($user, $siteId) {
                        return Appareil::query()
                            ->visibleBy($user, $siteId)
                            ->enService()
                            ->pluck('nom', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\TextInput::make('pourcentage_energie')
                    ->label(__('appareil.energie.pourcentage'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                    
                Forms\Components\TimePicker::make('heure_releve')
                    ->label(__('appareil.energie.heure_releve'))
                    ->required()
                    ->seconds(false)
                    ->native(false)
                    ->default(now()->format('H:i')),
                    
                Forms\Components\Textarea::make('observations')
                    ->label(__('appareil.energie.observations'))
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $permanence = $this->getOwnerRecord();
        $isLocked = $permanence->isLocked();
        
        // Sous-officier peut créer seulement si pas verrouillé et affecté
        $canCreate = false;
        if ($user->isAdmin()) {
            $canCreate = true;
        } elseif ($user->isSousOfficier() && !$isLocked) {
            $canCreate = $permanence->sousOfficiers()->where('users.id', $user->id)->exists();
        }

        return $table
            ->recordTitleAttribute('appareil.nom')
            // CLOISONNEMENT : Filtrer par utilisateur
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                return $query->visibleBy($user);
            })
            ->columns([
                Tables\Columns\TextColumn::make('appareil.nom')
                    ->label(__('appareil.fields.nom'))
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('pourcentage_energie')
                    ->label(__('appareil.energie.pourcentage'))
                    ->suffix('%')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 70 => 'success',
                        $state >= 30 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('heure_releve')
                    ->label(__('appareil.energie.heure_releve'))
                    ->time('H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sousOfficier.nom_complet')
                    ->label(__('appareil.energie.auteur'))
                    ->sortable()
                    // Masquer pour sous-officier (ne voit que ses propres)
                    ->visible(fn () => !$user->isSousOfficier()),
                    
                Tables\Columns\TextColumn::make('observations')
                    ->label(__('appareil.energie.observations'))
                    ->limit(30)
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.dates.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('heure_releve', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('appareil_id')
                    ->label(__('appareil.fields.nom'))
                    ->relationship('appareil', 'nom')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('appareil.energie.add'))
                    ->visible($canCreate)
                    ->mutateFormDataUsing(function (array $data) use ($user): array {
                        $data['sous_officier_id'] = $user->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (ReleveEnergie $record) => $record->canBeEditedBy($user)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (ReleveEnergie $record) => $record->canBeDeletedBy($user)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $user->isAdmin()),
                ]),
            ])
            ->emptyStateHeading(__('appareil.energie.no_releve'))
            ->emptyStateDescription(null);
    }
}

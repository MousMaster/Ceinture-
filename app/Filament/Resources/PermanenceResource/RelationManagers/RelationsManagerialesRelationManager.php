<?php

namespace App\Filament\Resources\PermanenceResource\RelationManagers;

use App\Enums\UserType;
use App\Models\Permanence;
use App\Models\RelationManageriale;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RelationsManagerialesRelationManager extends RelationManager
{
    protected static string $relationship = 'relationsManageriales';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('permanence.sections.relation_manageriale');
    }

    public static function getModelLabel(): string
    {
        return __('permanence.relation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('permanence.relation.plural');
    }

    /**
     * Permet les actions de création/modification même sur la page View
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        $user = auth()->user();
        $permanence = $this->getOwnerRecord();

        return $form
            ->schema([
                Forms\Components\Section::make(__('permanence.sections.evenements'))
                    ->schema([
                        Forms\Components\TimePicker::make('heure_evenement')
                            ->label(__('permanence.relation.heure_evenement'))
                            ->required()
                            ->seconds(false)
                            ->native(false),
                        Forms\Components\Select::make('sous_officier_id')
                            ->label(__('permanence.relation.auteur'))
                            ->options(function () use ($permanence, $user) {
                                // Si sous-officier, seulement lui-même
                                if ($user->isSousOfficier()) {
                                    return [$user->id => $user->nom_complet];
                                }
                                
                                // Pour admin/officier : inclure l'officier ET les sous-officiers affectés
                                $options = collect();
                                
                                // Ajouter l'officier responsable
                                $options[$permanence->officier->id] = $permanence->officier->nom_complet . ' (Officier)';
                                
                                // Ajouter les sous-officiers affectés
                                foreach ($permanence->sousOfficiers as $so) {
                                    $options[$so->id] = $so->nom_complet;
                                }
                                
                                return $options;
                            })
                            ->default(function () use ($user, $permanence) {
                                if ($user->isSousOfficier()) {
                                    return $user->id;
                                }
                                if ($user->isOfficier() && $permanence->officier_id === $user->id) {
                                    return $user->id;
                                }
                                return null;
                            })
                            ->disabled(fn () => $user->isSousOfficier())
                            ->dehydrated(true)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make(__('permanence.relation.label'))
                    ->schema([
                        Forms\Components\Textarea::make('evenement')
                            ->label(__('permanence.relation.evenement'))
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('effets_ordonnes')
                            ->label(__('permanence.relation.effets_ordonnes'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('observations')
                            ->label(__('permanence.relation.observations'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $permanence = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('evenement')
            // CLOISONNEMENT STRICT : Sous-officier ne voit que SES propres saisies
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                if ($user->isSousOfficier()) {
                    // Filtre strict : uniquement les événements créés par ce sous-officier
                    $query->where('sous_officier_id', $user->id);
                }
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('heure_evenement')
                    ->label(__('permanence.relation.heure_evenement'))
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sousOfficier.nom_complet')
                    ->label(__('permanence.relation.auteur'))
                    ->searchable(['nom', 'prenom'])
                    ->description(fn (RelationManageriale $record) => 
                        $record->sous_officier_id === $this->getOwnerRecord()->officier_id 
                            ? __('users.types.officier') 
                            : __('users.types.sous_officier')
                    ),
                Tables\Columns\TextColumn::make('evenement')
                    ->label(__('permanence.relation.evenement'))
                    ->limit(50)
                    ->tooltip(fn (RelationManageriale $record) => $record->evenement)
                    ->searchable(),
                Tables\Columns\TextColumn::make('effets_ordonnes')
                    ->label(__('permanence.relation.effets_ordonnes'))
                    ->limit(30)
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('permanence.relation.saisi_le'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('heure_evenement')
            ->filters([
                // Filtre auteur visible UNIQUEMENT pour Admin et Officier
                // Sous-officier ne peut pas voir les autres auteurs
                Tables\Filters\SelectFilter::make('sous_officier_id')
                    ->label(__('permanence.relation.auteur'))
                    ->options(function () use ($permanence) {
                        $options = collect();
                        $options[$permanence->officier->id] = $permanence->officier->nom_complet . ' (Officier)';
                        foreach ($permanence->sousOfficiers as $so) {
                            $options[$so->id] = $so->nom_complet;
                        }
                        return $options;
                    })
                    ->visible(fn () => !$user->isSousOfficier()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('permanence.actions.add_event'))
                    ->visible(fn () => $this->canCreate()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (RelationManageriale $record) => $this->canEditRecord($record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (RelationManageriale $record) => $this->canDeleteRecord($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $user->isAdmin()),
                ]),
            ])
            ->emptyStateHeading(__('permanence.messages.no_events'))
            ->emptyStateDescription($permanence->isLocked() 
                ? __('permanence.messages.locked') 
                : __('permanence.actions.add_event'))
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('permanence.actions.add_event'))
                    ->visible(fn () => $this->canCreate()),
            ]);
    }

    /**
     * Vérifie si l'utilisateur peut créer des événements
     */
    protected function canCreate(): bool
    {
        $user = auth()->user();
        $permanence = $this->getOwnerRecord();

        // Permanence validée = pas de création (sauf admin)
        if ($permanence->isLocked() && !$user->isAdmin()) {
            return false;
        }

        // Admin peut toujours créer
        if ($user->isAdmin()) {
            return true;
        }

        // Officier responsable peut créer
        if ($user->isOfficier() && $permanence->officier_id === $user->id) {
            return true;
        }

        // Sous-officier affecté peut créer
        if ($user->isSousOfficier()) {
            return $user->isAffectedToPermanence($permanence);
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur peut modifier un événement
     */
    protected function canEditRecord(RelationManageriale $record): bool
    {
        return $record->canBeEditedBy(auth()->user());
    }

    /**
     * Vérifie si l'utilisateur peut supprimer un événement
     */
    protected function canDeleteRecord(RelationManageriale $record): bool
    {
        return $record->canBeDeletedBy(auth()->user());
    }

}

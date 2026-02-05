<?php

namespace App\Filament\Resources\PermanenceResource\RelationManagers;

use App\Enums\DestinataireAppareil;
use App\Enums\EtatFonctionnement;
use App\Models\Appareil;
use App\Models\ReceptionMateriel;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Relation Manager pour la réception du matériel.
 * 
 * CLOISONNEMENT STRICT :
 * - Visible et éditable uniquement par admin/officier
 * - Sous-officier NE PEUT PAS saisir (c'est l'officier qui saisit)
 * - Chef de poste N'A PAS de matériel à recevoir
 * - Filtrage des appareils selon le destinataire (officier/opérateur)
 */
class ReceptionMaterielsRelationManager extends RelationManager
{
    protected static string $relationship = 'receptionMateriels';

    protected static ?string $recordTitleAttribute = 'appareil.nom';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('materiel.reception.title');
    }

    /**
     * VISIBILITÉ : Admin, Officier et Viewer uniquement.
     * Sous-officier NE VOIT PAS cette section.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();
        
        // Admin, officier et viewer peuvent voir
        if ($user->isAdmin() || $user->isOfficier() || $user->isViewer()) {
            return true;
        }
        
        // Sous-officier ne voit pas cette section
        return false;
    }

    public function form(Form $form): Form
    {
        $permanence = $this->getOwnerRecord();
        
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('materiel.reception.fields.destinataire'))
                    ->options(function () use ($permanence) {
                        // Liste : l'officier de la permanence + les sous-officiers opérateurs affectés
                        $options = [];
                        
                        // Officier de la permanence
                        if ($permanence->officier) {
                            $options[$permanence->officier_id] = $permanence->officier->nom_complet . ' (' . __('materiel.destinataires.officier') . ')';
                        }
                        
                        // Sous-officiers opérateurs affectés (pas les chefs de poste)
                        $operateurs = $permanence->sousOfficiers()
                            ->where('fonction', 'operateur')
                            ->get();
                        
                        foreach ($operateurs as $operateur) {
                            $options[$operateur->id] = $operateur->nom_complet . ' (' . __('materiel.destinataires.operateur') . ')';
                        }
                        
                        return $options;
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('appareil_id', null))
                    ->helperText(__('materiel.reception.helpers.destinataire')),
                    
                Forms\Components\Select::make('appareil_id')
                    ->label(__('materiel.reception.fields.appareil'))
                    ->options(function (Get $get) use ($permanence) {
                        $userId = $get('user_id');
                        if (!$userId) {
                            return [];
                        }
                        
                        // Déterminer le destinataire selon l'utilisateur sélectionné
                        $selectedUser = User::find($userId);
                        if (!$selectedUser) {
                            return [];
                        }
                        
                        $destinataire = $selectedUser->isOfficier() 
                            ? DestinataireAppareil::Officier 
                            : DestinataireAppareil::Operateur;
                        
                        // Exclure les appareils déjà reçus par cette personne pour cette permanence
                        $existingAppareilIds = ReceptionMateriel::where('permanence_id', $permanence->id)
                            ->where('user_id', $userId)
                            ->pluck('appareil_id')
                            ->toArray();
                        
                        return Appareil::query()
                            ->active()
                            ->enService()
                            ->where('destinataire', $destinataire)
                            ->whereNotIn('id', $existingAppareilIds)
                            ->pluck('nom', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText(__('materiel.reception.helpers.appareil')),
                    
                Forms\Components\Toggle::make('recu_integralite')
                    ->label(__('materiel.reception.fields.recu_integralite'))
                    ->default(true)
                    ->inline(false)
                    ->helperText(__('materiel.reception.helpers.recu_integralite')),
                    
                Forms\Components\Select::make('etat_fonctionnement')
                    ->label(__('materiel.reception.fields.etat_fonctionnement'))
                    ->options(EtatFonctionnement::forSelect())
                    ->default(EtatFonctionnement::Fonctionne->value)
                    ->required(),
                    
                Forms\Components\Textarea::make('commentaire')
                    ->label(__('materiel.reception.fields.commentaire'))
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $permanence = $this->getOwnerRecord();
        $isLocked = $permanence->isLocked();
        
        // Seuls admin et officier responsable peuvent créer (pas viewer, pas sous-officier)
        $canCreate = false;
        if ($user->isAdmin()) {
            $canCreate = !$isLocked || $user->isAdmin();
        } elseif ($user->isOfficier() && !$isLocked) {
            $canCreate = $permanence->officier_id === $user->id;
        }

        return $table
            ->recordTitleAttribute('appareil.nom')
            ->columns([
                Tables\Columns\TextColumn::make('user.nom_complet')
                    ->label(__('materiel.reception.fields.destinataire'))
                    ->description(fn (ReceptionMateriel $record): string => 
                        $record->user->isOfficier() 
                            ? __('materiel.destinataires.officier')
                            : __('materiel.destinataires.operateur')
                    )
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('appareil.nom')
                    ->label(__('materiel.reception.fields.appareil'))
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('recu_integralite')
                    ->label(__('materiel.reception.fields.recu_integralite'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('etat_fonctionnement')
                    ->label(__('materiel.reception.fields.etat_fonctionnement'))
                    ->badge()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('commentaire')
                    ->label(__('materiel.reception.fields.commentaire'))
                    ->limit(30)
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.dates.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('user_id')
            ->groups([
                Tables\Grouping\Group::make('user.nom_complet')
                    ->label(__('materiel.reception.fields.destinataire'))
                    ->collapsible(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('materiel.reception.fields.destinataire'))
                    ->relationship('user', 'nom')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('etat_fonctionnement')
                    ->label(__('materiel.reception.fields.etat_fonctionnement'))
                    ->options(EtatFonctionnement::forSelect()),
                Tables\Filters\TernaryFilter::make('recu_integralite')
                    ->label(__('materiel.reception.fields.recu_integralite')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('materiel.reception.actions.add'))
                    ->visible($canCreate),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => !$user->isViewer() && (!$isLocked || $user->isAdmin())),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => !$user->isViewer() && (!$isLocked || $user->isAdmin())),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $user->isAdmin()),
                ]),
            ])
            ->emptyStateHeading(__('materiel.reception.no_reception'))
            ->emptyStateDescription(__('materiel.reception.no_reception_desc'));
    }
}

<?php

namespace App\Providers;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\ServiceProvider;

/**
 * Provider pour patcher les problèmes connus de Filament Forms.
 * 
 * PROBLÈME : FileUpload::getValue() peut retourner `false` au lieu d'un array,
 * ce qui provoque des erreurs "foreach() argument must be of type array|object, false given".
 * 
 * SOLUTION : Ajouter un `dehydrateStateUsing` et `afterStateHydrated` global
 * pour normaliser les valeurs.
 */
class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureFileUpload();
    }

    /**
     * Configure FileUpload pour éviter les erreurs de type.
     */
    protected function configureFileUpload(): void
    {
        // Macro pour normaliser l'état du FileUpload
        FileUpload::macro('normalizeState', function () {
            /** @var FileUpload $this */
            return $this
                ->afterStateHydrated(function (FileUpload $component, $state) {
                    // Normalise à l'hydratation
                    if ($state === false || (!is_array($state) && !is_string($state) && $state !== null)) {
                        $component->state(null);
                    }
                })
                ->dehydrateStateUsing(function ($state) {
                    // Normalise à la déshydratation (avant sauvegarde)
                    if ($state === false) {
                        return null;
                    }
                    if (is_array($state)) {
                        return array_filter($state);
                    }
                    return $state;
                });
        });

        // Configuration globale par défaut pour tous les FileUpload
        FileUpload::configureUsing(function (FileUpload $fileUpload): void {
            $fileUpload
                ->afterStateHydrated(function (FileUpload $component, $state) {
                    // Évite l'erreur foreach sur false
                    if ($state === false || $state === 0 || $state === '0') {
                        $component->state(null);
                    } elseif (!is_array($state) && !is_string($state) && $state !== null) {
                        $component->state(null);
                    }
                });
        });
    }
}

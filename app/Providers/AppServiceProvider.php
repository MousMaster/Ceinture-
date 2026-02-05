<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurer la direction RTL pour Filament
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => $this->getRtlStyles(),
        );

        // Configurer la direction du document
        FilamentView::registerRenderHook(
            'panels::body.start',
            fn (): string => $this->getDirectionScript(),
        );
    }

    /**
     * Génère les styles RTL si nécessaire
     */
    protected function getRtlStyles(): string
    {
        if (App::getLocale() !== 'ar') {
            return '';
        }

        return <<<'HTML'
        <style>
            /* RTL Support for Arabic */
            [dir="rtl"] .fi-sidebar-nav-groups {
                direction: rtl;
            }
            [dir="rtl"] .fi-ta-text {
                text-align: right;
            }
            [dir="rtl"] .fi-fo-field-wrp {
                text-align: right;
            }
            [dir="rtl"] .fi-btn {
                flex-direction: row-reverse;
            }
            [dir="rtl"] .fi-breadcrumbs {
                flex-direction: row-reverse;
            }
            [dir="rtl"] .fi-header-heading {
                text-align: right;
            }
            [dir="rtl"] table th,
            [dir="rtl"] table td {
                text-align: right;
            }
        </style>
        HTML;
    }

    /**
     * Génère le script pour définir la direction
     */
    protected function getDirectionScript(): string
    {
        $dir = App::getLocale() === 'ar' ? 'rtl' : 'ltr';
        
        return <<<HTML
        <script>
            document.documentElement.dir = '{$dir}';
            document.documentElement.lang = '" . App::getLocale() . "';
        </script>
        HTML;
    }
}

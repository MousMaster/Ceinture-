<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Change la langue de l'application
     */
    public function switch(string $locale): RedirectResponse
    {
        // Vérifier que la langue est supportée
        if (!in_array($locale, SetLocale::SUPPORTED_LOCALES)) {
            $locale = 'fr';
        }

        // Stocker la langue en session
        Session::put('locale', $locale);

        // Rediriger vers la page précédente
        return redirect()->back();
    }

    /**
     * Récupère les langues disponibles
     */
    public static function getAvailableLocales(): array
    {
        return [
            'fr' => [
                'name' => 'Français',
                'native' => 'Français',
                'flag' => '🇫🇷',
                'dir' => 'ltr',
            ],
            'ar' => [
                'name' => 'Arabic',
                'native' => 'العربية',
                'flag' => '🇸🇦',
                'dir' => 'rtl',
            ],
        ];
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Langues supportées
     */
    public const SUPPORTED_LOCALES = ['fr', 'ar'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Récupérer la langue depuis la session ou utiliser la langue par défaut
        $locale = Session::get('locale', config('app.locale', 'fr'));

        // Vérifier que la langue est supportée
        if (!in_array($locale, self::SUPPORTED_LOCALES)) {
            $locale = 'fr';
        }

        // Appliquer la langue
        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Vérifie si la langue actuelle est RTL
     */
    public static function isRtl(): bool
    {
        return App::getLocale() === 'ar';
    }

    /**
     * Récupère la direction du texte
     */
    public static function getDirection(): string
    {
        return self::isRtl() ? 'rtl' : 'ltr';
    }
}

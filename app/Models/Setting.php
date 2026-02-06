<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
    ];

    /**
     * Mutateur pour normaliser la valeur selon le type.
     * - file: retourne toujours une string ou null (jamais false/boolean)
     * - boolean: retourne un booléen
     * - autres: retourne la valeur telle quelle
     */
    protected function value(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                // Normalisation à la lecture selon le type
                if ($this->type === 'file') {
                    // File doit être une string valide ou null
                    if ($value === false || $value === 'false' || $value === '' || $value === '[]') {
                        return null;
                    }
                    return is_string($value) ? $value : null;
                }

                if ($this->type === 'boolean') {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }

                return $value;
            },
            set: function ($value) {
                // Normalisation à l'écriture selon le type
                if ($this->type === 'file') {
                    // Ne jamais stocker false pour un fichier
                    if ($value === false || $value === 'false' || $value === []) {
                        return null;
                    }
                    // Si c'est un tableau (Livewire), prendre le premier élément
                    if (is_array($value)) {
                        return !empty($value) ? reset($value) : null;
                    }
                    return is_string($value) && $value !== '' ? $value : null;
                }

                if ($this->type === 'boolean') {
                    return $value ? '1' : '0';
                }

                // Pour les autres types, éviter de stocker false
                if ($value === false) {
                    return null;
                }

                return $value;
            },
        );
    }

    /**
     * Récupère une valeur de paramètre
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    }

    /**
     * Définit une valeur de paramètre
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Récupère tous les paramètres d'un groupe
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Récupère le chemin complet d'un logo
     */
    public static function getLogoPath(string $key): ?string
    {
        $value = static::get($key);
        
        if (!$value) {
            return null;
        }

        $path = storage_path("app/public/{$value}");
        
        if (file_exists($path)) {
            return $path;
        }

        return null;
    }

    /**
     * Récupère le logo en base64 pour le PDF
     */
    public static function getLogoBase64(string $key): ?string
    {
        $path = static::getLogoPath($key);
        
        if (!$path) {
            return null;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}

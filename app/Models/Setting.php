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

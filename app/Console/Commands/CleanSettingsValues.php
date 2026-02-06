<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CleanSettingsValues extends Command
{
    protected $signature = 'settings:clean';

    protected $description = 'Nettoie les valeurs invalides (false, "false") dans la table settings';

    public function handle(): int
    {
        $this->info('Nettoyage des valeurs invalides dans settings...');

        // Valeurs invalides pour un champ file
        $invalidValues = ['false', '0', '', '[]', '""', 'null'];

        // Compte les enregistrements à corriger
        $invalidCount = DB::table('settings')
            ->where('type', 'file')
            ->where(function ($query) use ($invalidValues) {
                $query->whereIn('value', $invalidValues)
                    ->orWhere('value', '');
            })
            ->count();

        if ($invalidCount === 0) {
            $this->info('Aucune valeur invalide trouvée.');
            return self::SUCCESS;
        }

        $this->warn("Trouvé {$invalidCount} enregistrement(s) avec des valeurs invalides.");

        // Corrige les valeurs pour les types file
        $updated = DB::table('settings')
            ->where('type', 'file')
            ->where(function ($query) use ($invalidValues) {
                $query->whereIn('value', $invalidValues)
                    ->orWhere('value', '');
            })
            ->update(['value' => null, 'updated_at' => now()]);

        $this->info("Corrigé {$updated} enregistrement(s).");

        // Vider le cache des settings
        $settings = DB::table('settings')->pluck('key');
        foreach ($settings as $key) {
            Cache::forget("setting.{$key}");
        }

        $this->info('Cache des settings vidé.');
        $this->info('Nettoyage terminé avec succès.');

        return self::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, text, boolean, file
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        // Insérer les paramètres par défaut
        $settings = [
            // Institution
            ['key' => 'institution_name', 'value' => 'MINISTÈRE DE LA DÉFENSE', 'type' => 'string', 'group' => 'institution', 'label' => 'Nom de l\'institution'],
            ['key' => 'direction_name', 'value' => 'DIRECTION GÉNÉRALE', 'type' => 'string', 'group' => 'institution', 'label' => 'Nom de la direction'],
            ['key' => 'system_name', 'value' => 'SYSTÈME DE GESTION DES REGISTRES DE PERMANENCE', 'type' => 'string', 'group' => 'institution', 'label' => 'Nom du système'],
            
            // Logos
            ['key' => 'logo_institution', 'value' => null, 'type' => 'file', 'group' => 'logos', 'label' => 'Logo institution (gauche)'],
            ['key' => 'logo_direction', 'value' => null, 'type' => 'file', 'group' => 'logos', 'label' => 'Logo direction (droite)'],
            
            // PDF
            ['key' => 'pdf_title', 'value' => 'REGISTRE DE PERMANENCE', 'type' => 'string', 'group' => 'pdf', 'label' => 'Titre du document PDF'],
            ['key' => 'pdf_footer', 'value' => 'Document officiel - Ne pas reproduire sans autorisation', 'type' => 'string', 'group' => 'pdf', 'label' => 'Pied de page PDF'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

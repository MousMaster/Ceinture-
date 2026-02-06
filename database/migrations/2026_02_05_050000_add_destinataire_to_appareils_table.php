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
        Schema::table('appareils', function (Blueprint $table) {
            // Destinataire de l'appareil : 'officier' ou 'operateur'
            // Permet de filtrer les appareils selon le rôle
            $table->string('destinataire', 20)->nullable()->after('categorie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appareils', function (Blueprint $table) {
            $table->dropColumn('destinataire');
        });
    }
};

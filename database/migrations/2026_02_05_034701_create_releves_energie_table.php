<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table des relevés d'énergie - CLOISONNEMENT SOUS-OFFICIER
     * Le sous-officier ne voit que ses propres relevés.
     */
    public function up(): void
    {
        Schema::create('releves_energie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permanence_id')->constrained('permanences')->cascadeOnDelete();
            $table->foreignId('appareil_id')->constrained('appareils')->cascadeOnDelete();
            $table->foreignId('sous_officier_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('pourcentage_energie'); // 0-100
            $table->time('heure_releve');
            $table->text('observations')->nullable();
            $table->timestamps();

            // Index pour le cloisonnement et les performances
            $table->index(['permanence_id', 'sous_officier_id']);
            $table->index(['permanence_id', 'appareil_id']);
            $table->index('sous_officier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('releves_energie');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table des redémarrages d'appareils - CLOISONNEMENT OFFICIER
     * Totalement INVISIBLE pour les sous-officiers.
     * Visible uniquement par officier responsable et admin.
     */
    public function up(): void
    {
        Schema::create('redemarrages_appareils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permanence_id')->constrained('permanences')->cascadeOnDelete();
            $table->foreignId('appareil_id')->constrained('appareils')->cascadeOnDelete();
            $table->foreignId('officier_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('nombre_redemarrages')->default(1);
            $table->string('motif'); // Motif du redémarrage
            $table->time('heure_debut');
            $table->time('heure_fin')->nullable();
            $table->text('decision_officier')->nullable(); // Décision ou commentaire
            $table->timestamps();

            // Index pour les performances
            $table->index(['permanence_id', 'officier_id']);
            $table->index(['permanence_id', 'appareil_id']);
            $table->index('officier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redemarrages_appareils');
    }
};

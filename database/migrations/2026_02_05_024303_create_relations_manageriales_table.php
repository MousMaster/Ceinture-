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
        Schema::create('relations_manageriales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permanence_id')->constrained('permanences')->onDelete('cascade');
            $table->foreignId('sous_officier_id')->constrained('users')->onDelete('restrict');
            $table->time('heure_evenement');
            $table->text('evenement');
            $table->text('effets_ordonnes')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();

            // Index pour les requêtes fréquentes
            $table->index(['permanence_id', 'heure_evenement']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relations_manageriales');
    }
};

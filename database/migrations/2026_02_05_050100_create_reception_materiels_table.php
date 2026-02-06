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
        Schema::create('reception_materiels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permanence_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appareil_id')->constrained()->cascadeOnDelete();
            $table->boolean('recu_integralite')->default(false);
            $table->string('etat_fonctionnement', 20)->default('fonctionne');
            $table->text('commentaire')->nullable();
            $table->timestamps();

            // Un appareil ne peut être reçu qu'une seule fois par personne par permanence
            $table->unique(['permanence_id', 'user_id', 'appareil_id'], 'reception_unique');

            // Index pour les requêtes fréquentes
            $table->index(['permanence_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reception_materiels');
    }
};

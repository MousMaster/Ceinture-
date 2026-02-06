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
        Schema::create('appareils', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('type')->nullable(); // Type/catégorie de l'appareil
            $table->string('categorie')->nullable(); // Catégorie supplémentaire
            $table->string('numero_serie')->nullable(); // Numéro de série
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('statut')->default('actif'); // actif, hors_service
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['statut', 'is_active']);
            $table->index('site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appareils');
    }
};

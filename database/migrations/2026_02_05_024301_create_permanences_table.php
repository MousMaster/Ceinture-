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
        Schema::create('permanences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officier_id')->constrained('users')->onDelete('restrict');
            $table->date('date');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->enum('statut', ['planifiee', 'en_cours', 'validee'])->default('planifiee');
            $table->text('commentaire_officier')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            // Une seule permanence par date
            $table->unique('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permanences');
    }
};

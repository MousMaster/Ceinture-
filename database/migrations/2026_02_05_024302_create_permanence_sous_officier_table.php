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
        Schema::create('permanence_sous_officier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permanence_id')->constrained('permanences')->onDelete('cascade');
            $table->foreignId('sous_officier_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('site_id')->constrained('sites')->onDelete('restrict');
            $table->timestamps();

            // Un sous-officier ne peut être affecté qu'une fois par permanence et par site
            $table->unique(['permanence_id', 'sous_officier_id', 'site_id'], 'pso_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permanence_sous_officier');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activites_adherents')) return;

        Schema::create('activites_adherents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_adherent')->constrained('adherents')->cascadeOnDelete();
            $table->foreignId('id_activite')->constrained('activites')->cascadeOnDelete();
            $table->date('date_entree');
            $table->date('date_sortie')->nullable();
            $table->string('motif_sortie')->nullable();
            $table->boolean('est_un_abandon')->default(false);
            $table->string('saison')->nullable()->default('2025-2026');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites_adherents');
    }
};

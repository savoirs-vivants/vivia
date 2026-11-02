<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activites')) return;

        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('type', 50)->default('activite');
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->integer('tarif')->nullable();
            $table->longText('horaires')->nullable();
            $table->timestamps();
        });
        // description, max_eleves, classes, is_archived et id_dossier sont ajoutés par
        // leurs migrations dédiées respectives (plus bas dans l'historique).
    }

    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};

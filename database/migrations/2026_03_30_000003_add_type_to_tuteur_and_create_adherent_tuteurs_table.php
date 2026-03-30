<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter le type de tuteur (parent, personne autorisée, non autorisée)
        Schema::table('tuteur', function (Blueprint $table) {
            $table->enum('type', ['parent_tuteur', 'autre_autorise', 'non_autorise'])
                  ->default('parent_tuteur')
                  ->after('id');
        });

        // Table pivot pour relier plusieurs tuteurs à un même adhérent
        Schema::create('adherent_tuteurs', function (Blueprint $table) {
            $table->unsignedBigInteger('id_adherent');
            $table->unsignedBigInteger('id_tuteur');

            $table->foreign('id_adherent')->references('id')->on('adherents')->onDelete('cascade');
            $table->foreign('id_tuteur')->references('id')->on('tuteur')->onDelete('cascade');

            $table->primary(['id_adherent', 'id_tuteur']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherent_tuteurs');

        Schema::table('tuteur', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

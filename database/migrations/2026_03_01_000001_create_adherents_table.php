<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('adherents')) return;

        Schema::create('adherents', function (Blueprint $table) {
            $table->id();
            $table->string('numero_adherent')->nullable()->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('carnet')->nullable();
            $table->date('date_naiss')->nullable();
            $table->integer('age')->nullable();
            $table->string('genre', 50)->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal', 20)->nullable();
            $table->string('tel')->nullable();
            $table->string('mail')->nullable();
            $table->string('occupation')->nullable();
            $table->string('etablissement')->nullable();
            $table->string('regime_social')->nullable();
            $table->text('idee_metier')->nullable();
            $table->text('decouverte_metier')->nullable();
            $table->text('actions')->nullable();
            $table->longText('commentaire')->nullable();
            $table->boolean('manif')->default(false);
            $table->boolean('communication')->default(false);
            $table->longText('bulletin')->nullable();
            $table->mediumText('signature')->nullable();
            $table->text('problemes_sante')->nullable();
            $table->text('allergies')->nullable();
            $table->text('conduite_a_tenir')->nullable();
            $table->text('restrictions_alimentaires')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherents');
    }
};

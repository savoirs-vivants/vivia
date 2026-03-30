<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adherents_structure', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('sigle')->nullable();
            $table->string('adresse')->nullable();
            $table->string('code_postal', 20)->nullable();
            $table->string('ville')->nullable();
            $table->date('date_creation')->nullable();
            $table->string('tel')->nullable();
            $table->string('tel_portable')->nullable();
            $table->string('mail')->nullable();
            $table->string('site_web')->nullable();
            $table->string('nom_correspondant')->nullable();
            $table->string('tel_correspondant')->nullable();
            $table->boolean('bulletin')->default(false);
            $table->boolean('communication')->default(false);
            $table->boolean('autorisation_photo')->default(false);
            $table->enum('statut', ['soutien', 'ressourcerie', 'participation'])->nullable();
            $table->string('statut_juridique')->nullable();
            $table->text('signature')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherents_structure');
    }
};

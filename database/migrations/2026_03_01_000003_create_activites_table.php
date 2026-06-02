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
            $table->text('description')->nullable();
            $table->string('type', 50)->default('activite');
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->integer('tarif')->nullable();
            $table->smallInteger('max_eleves')->unsigned()->nullable();
            $table->longText('horaires')->nullable();
            $table->longText('classes')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->foreignId('id_dossier')->nullable()->constrained('dossiers_activite')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};

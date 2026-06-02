<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seances')) return;

        Schema::create('seances', function (Blueprint $table) {
            $table->unsignedBigInteger('id_seance')->autoIncrement();
            $table->foreignId('id_activite')->constrained('activites')->cascadeOnDelete();
            $table->dateTime('date');
            $table->string('statut')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};

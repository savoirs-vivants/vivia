<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('presence')) return;

        Schema::create('presence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_adherent')->constrained('adherents')->cascadeOnDelete();
            $table->unsignedBigInteger('id_seance');
            $table->string('statut')->nullable();
            $table->text('raison')->nullable();
            $table->timestamps();

            $table->foreign('id_seance')->references('id_seance')->on('seances')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presence');
    }
};

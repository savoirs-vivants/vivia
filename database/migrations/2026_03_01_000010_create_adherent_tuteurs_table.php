<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('adherent_tuteurs')) return;

        Schema::create('adherent_tuteurs', function (Blueprint $table) {
            $table->foreignId('id_adherent')->constrained('adherents')->cascadeOnDelete();
            $table->foreignId('id_tuteur')->constrained('tuteur')->cascadeOnDelete();

            $table->primary(['id_adherent', 'id_tuteur']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherent_tuteurs');
    }
};

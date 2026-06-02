<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activites_gestionnaire')) return;

        Schema::create('activites_gestionnaire', function (Blueprint $table) {
            $table->foreignId('id_activite')->constrained('activites')->cascadeOnDelete();
            $table->foreignId('id_users')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['id_activite', 'id_users']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites_gestionnaire');
    }
};

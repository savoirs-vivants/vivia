<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ressourcerie', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->text('condition_location')->nullable();
            $table->decimal('prix', 8, 2)->default(0);
            $table->enum('type_tarif', ['tarif_particulier', 'tarif_structure', 'tarif_scolaire']);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ressourcerie');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inscriptions')) return;

        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_adherent')->nullable()->constrained('adherents')->nullOnDelete();
            $table->foreignId('id_structure')->nullable()->constrained('adherents_structure')->nullOnDelete();
            $table->string('saison', 50);
            $table->date('date_inscription');
            $table->string('type_adhesion', 50)->default('classique');
            $table->json('ressourceries_ids')->nullable();
            $table->json('types_activite')->nullable();
            $table->string('a_paye')->nullable();
            $table->decimal('montant', 10, 2)->nullable();
            $table->boolean('renouvellement')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('paiement')) return;

        Schema::create('paiement', function (Blueprint $table) {
            $table->unsignedBigInteger('id_paiement')->autoIncrement();
            $table->foreignId('id_adherent')->nullable()->constrained('adherents')->nullOnDelete();
            $table->unsignedBigInteger('id_structure')->nullable();
            $table->decimal('montant', 10, 2);
            $table->string('source')->nullable();
            $table->date('date_paiement')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->foreign('id_structure')->references('id')->on('adherents_structure')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            // Rendre id_adherent nullable — la FK existante s'appelle inscriptions_ibfk_1
            $table->dropForeign('inscriptions_ibfk_1');
            $table->unsignedBigInteger('id_adherent')->nullable()->change();
            $table->foreign('id_adherent')->references('id')->on('adherents')->onDelete('cascade');

            // Lier à la table des structures
            $table->foreignId('id_structure')
                ->nullable()
                ->after('id_adherent')
                ->constrained('adherents_structure')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropForeign(['id_structure']);
            $table->dropColumn('id_structure');

            $table->dropForeign(['id_adherent']);
            $table->unsignedBigInteger('id_adherent')->nullable(false)->change();
            $table->foreign('id_adherent')->references('id')->on('adherents')->onDelete('cascade');
        });
    }
};

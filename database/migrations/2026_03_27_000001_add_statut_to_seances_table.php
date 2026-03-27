<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->string('statut')->nullable()->after('date');
            // Valeurs : null = planifiée, 'appel_fait' = appel enregistré, 'terminee' = séance clôturée
        });
    }

    public function down(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};

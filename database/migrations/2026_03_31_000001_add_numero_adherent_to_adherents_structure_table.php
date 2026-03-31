<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adherents_structure', function (Blueprint $table) {
            $table->string('numero_adherent')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('adherents_structure', function (Blueprint $table) {
            $table->dropColumn('numero_adherent');
        });
    }
};

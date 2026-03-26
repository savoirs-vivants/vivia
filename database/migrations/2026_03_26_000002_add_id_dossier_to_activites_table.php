<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->foreignId('id_dossier')
                ->nullable()
                ->after('is_archived')
                ->constrained('dossiers_activite')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->dropForeign(['id_dossier']);
            $table->dropColumn('id_dossier');
        });
    }
};

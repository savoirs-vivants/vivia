<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // signature peut contenir un data-URL base64 (> 255 caractères)
        Schema::table('adherents', function (Blueprint $table) {
            $table->mediumText('signature')->nullable()->change();
        });

        // renouvellement est dans le modèle Inscription mais absent de la table
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->boolean('renouvellement')->default(false)->after('montant');
        });
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn('renouvellement');
        });

        Schema::table('adherents', function (Blueprint $table) {
            $table->string('signature')->nullable()->change();
        });
    }
};

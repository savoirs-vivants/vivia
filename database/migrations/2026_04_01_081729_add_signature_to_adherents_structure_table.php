<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adherents_structure', function (Blueprint $table) {
            $table->text('signature')->nullable()->after('autorisation_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adherents_structure', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }
};

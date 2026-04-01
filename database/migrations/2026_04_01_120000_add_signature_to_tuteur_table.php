<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tuteur', function (Blueprint $table) {
            $table->date('date_signature')->nullable()->after('profession');
            $table->mediumText('signature')->nullable()->after('date_signature');
        });
    }

    public function down(): void
    {
        Schema::table('tuteur', function (Blueprint $table) {
            $table->dropColumn(['date_signature', 'signature']);
        });
    }
};

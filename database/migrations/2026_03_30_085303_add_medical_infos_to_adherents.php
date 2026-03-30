<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('adherents', function (Blueprint $table) {
        $table->text('problemes_sante')->nullable();
        $table->text('allergies')->nullable();
        $table->text('conduite_a_tenir')->nullable();
        $table->text('restrictions_alimentaires')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            //
        });
    }
};

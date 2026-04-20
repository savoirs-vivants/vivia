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
        Schema::table('activites', function (Blueprint $table) {
            $table->text('description')->nullable()->after('nom');
        });
    }

    public function down()
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};

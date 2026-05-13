<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('adherents', function (Blueprint $table) {
            $table->json('bulletin')->nullable()->change();
        });

        Schema::table('adherents_structure', function (Blueprint $table) {
            $table->json('bulletin')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('adherents', function (Blueprint $table) {
            $table->boolean('bulletin')->default(false)->change();
        });

        Schema::table('adherents_structure', function (Blueprint $table) {
            $table->boolean('bulletin')->default(false)->change();
        });
    }
};

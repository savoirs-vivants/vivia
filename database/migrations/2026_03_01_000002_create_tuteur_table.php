<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tuteur')) return;

        Schema::create('tuteur', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['parent_tuteur', 'autre_autorise', 'non_autorise'])->default('parent_tuteur');
            $table->string('nom');
            $table->string('prenom');
            $table->string('mail')->nullable();
            $table->string('tel')->nullable();
            $table->boolean('adhere')->default(false);
            $table->boolean('rentre_fin')->default(false);
            $table->boolean('rentre_annul')->default(false);
            $table->string('profession')->nullable();
            $table->date('date_signature')->nullable();
            $table->mediumText('signature')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuteur');
    }
};

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
    Schema::create('sync_logs', function (Blueprint $table) {
        $table->id();
        $table->string('source')->default('helloasso');
        $table->string('status');
        $table->integer('payments_imported')->default(0);
        $table->json('errors')->nullable();
        $table->timestamps(); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};

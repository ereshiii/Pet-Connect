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
        Schema::create('slow_queries', function (Blueprint $table) {
            $table->id();
            $table->text('query');
            $table->json('bindings')->nullable();
            $table->integer('time_ms');
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->timestamps();
            
            $table->index(['time_ms', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slow_queries');
    }
};

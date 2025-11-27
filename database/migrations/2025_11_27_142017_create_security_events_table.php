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
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->string('ip_address', 45)->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'severity']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};

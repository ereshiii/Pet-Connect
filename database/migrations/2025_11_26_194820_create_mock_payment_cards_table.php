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
        Schema::create('mock_payment_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_id')->unique();
            $table->string('card_number');
            $table->string('card_holder');
            $table->string('expiry');
            $table->string('cvv');
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('bank');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mock_payment_cards');
    }
};

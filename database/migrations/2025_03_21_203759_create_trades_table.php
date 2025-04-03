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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->string('order_id')->nullable();
            $table->string('trade_id')->nullable();
            $table->enum('side', ['BUY', 'SELL'])->nullable();
            $table->string('price')->nullable();
            $table->string('amount')->nullable();
            $table->string('cost')->nullable();
            $table->string('realized_pnl')->nullable();
            $table->string('fee')->nullable();
            $table->string('fee_currency')->nullable();
            $table->timestamp('trade_time')->nullable();
            $table->boolean('maker')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};

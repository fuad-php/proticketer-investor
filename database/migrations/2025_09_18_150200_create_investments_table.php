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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('investor_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('profit_percentage', 5, 2);
            $table->decimal('expected_profit', 15, 2);
            $table->decimal('actual_profit', 15, 2)->nullable();
            $table->date('investment_date');
            $table->date('maturity_date');
            $table->enum('status', ['active', 'matured', 'cancelled'])->default('active');
            $table->enum('payment_status', ['pending', 'partial', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};

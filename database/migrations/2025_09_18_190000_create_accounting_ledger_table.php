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
        Schema::create('accounting_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->date('transaction_date');
            $table->string('transaction_type'); // investment, payout, fee, expense, revenue
            $table->string('category'); // investment_income, management_fee, performance_fee, operating_expense, etc.
            $table->string('description');
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->string('reference_type')->nullable(); // order, investment, inquiry, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('investor_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional transaction details
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, reversed
            $table->string('reversal_reason')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();

            $table->index(['transaction_date', 'transaction_type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['investor_id', 'transaction_date']);
            $table->index(['client_id', 'transaction_date']);
            $table->index('status');
            $table->index('created_by');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_ledger');
    }
};

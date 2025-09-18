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
        Schema::table('investments', function (Blueprint $table) {
            $table->decimal('management_fee', 15, 2)->default(0)->after('actual_profit');
            $table->decimal('performance_fee', 15, 2)->default(0)->after('management_fee');
            $table->decimal('net_profit', 15, 2)->default(0)->after('performance_fee');
            $table->decimal('total_return', 15, 2)->default(0)->after('net_profit');
            $table->decimal('return_percentage', 5, 2)->default(0)->after('total_return');
            $table->enum('payout_frequency', ['monthly', 'quarterly', 'semi_annual', 'annual', 'maturity'])->default('maturity')->after('return_percentage');
            $table->date('next_payout_date')->nullable()->after('payout_frequency');
            $table->decimal('payout_amount', 15, 2)->default(0)->after('next_payout_date');
            $table->boolean('auto_reinvest')->default(false)->after('payout_amount');
            $table->string('investment_reference')->nullable()->after('auto_reinvest');
            $table->json('payout_history')->nullable()->after('investment_reference');
            $table->timestamp('last_payout_at')->nullable()->after('payout_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn([
                'management_fee',
                'performance_fee',
                'net_profit',
                'total_return',
                'return_percentage',
                'payout_frequency',
                'next_payout_date',
                'payout_amount',
                'auto_reinvest',
                'investment_reference',
                'payout_history',
                'last_payout_at'
            ]);
        });
    }
};
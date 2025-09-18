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
        Schema::create('investment_statements', function (Blueprint $table) {
            $table->id();
            $table->string('statement_number')->unique();
            $table->foreignId('investor_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('investment_id')->constrained()->onDelete('cascade');
            $table->date('statement_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->decimal('total_investments', 15, 2)->default(0);
            $table->decimal('total_profits', 15, 2)->default(0);
            $table->decimal('total_payouts', 15, 2)->default(0);
            $table->decimal('management_fees', 15, 2)->default(0);
            $table->decimal('performance_fees', 15, 2)->default(0);
            $table->json('transaction_summary')->nullable();
            $table->enum('status', ['draft', 'approved', 'published', 'archived'])->default('draft');
            $table->string('pdf_path')->nullable();
            $table->string('digital_signature_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_statements');
    }
};
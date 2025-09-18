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
        Schema::table('inquiries', function (Blueprint $table) {
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('status');
            $table->timestamp('response_date')->nullable()->after('response');
            $table->decimal('quotation_amount', 15, 2)->nullable()->after('response_date');
            $table->date('quotation_valid_until')->nullable()->after('quotation_amount');
            $table->json('response_attachments')->nullable()->after('attachments');
            $table->text('internal_notes')->nullable()->after('response_attachments');
            $table->date('estimated_completion_date')->nullable()->after('internal_notes');
            $table->date('actual_completion_date')->nullable()->after('estimated_completion_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'priority',
                'response_date',
                'quotation_amount',
                'quotation_valid_until',
                'response_attachments',
                'internal_notes',
                'estimated_completion_date',
                'actual_completion_date'
            ]);
        });
    }
};
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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('product_category')->nullable()->after('description');
            $table->string('product_specs')->nullable()->after('product_category');
            $table->decimal('management_fee_percentage', 5, 2)->default(0)->after('profit_percentage');
            $table->decimal('performance_fee_percentage', 5, 2)->default(0)->after('management_fee_percentage');
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium')->after('performance_fee_percentage');
            $table->json('investor_list')->nullable()->after('risk_level');
            $table->json('supporting_documents')->nullable()->after('attachments');
            $table->string('statement_version')->default('1.0')->after('supporting_documents');
            $table->boolean('is_published')->default(false)->after('statement_version');
            $table->timestamp('published_at')->nullable()->after('is_published');
            $table->foreignId('published_by')->nullable()->constrained('users')->onDelete('set null')->after('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'product_category',
                'product_specs',
                'management_fee_percentage',
                'performance_fee_percentage',
                'risk_level',
                'investor_list',
                'supporting_documents',
                'statement_version',
                'is_published',
                'published_at',
                'published_by'
            ]);
        });
    }
};
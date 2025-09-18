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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('channel')->after('type')->default('system');
            $table->string('title')->after('channel');
            $table->text('message')->after('title');
            $table->string('recipient_type')->after('message');
            $table->unsignedBigInteger('recipient_id')->nullable()->after('recipient_type');
            $table->string('recipient_email')->nullable()->after('recipient_id');
            $table->string('recipient_phone')->nullable()->after('recipient_email');
            $table->string('status')->default('pending')->after('recipient_phone');
            $table->timestamp('scheduled_at')->nullable()->after('status');
            $table->timestamp('sent_at')->nullable()->after('scheduled_at');
            $table->timestamp('delivered_at')->nullable()->after('sent_at');
            $table->string('external_id')->nullable()->after('delivered_at');
            $table->text('error_message')->nullable()->after('external_id');
            $table->integer('retry_count')->default(0)->after('error_message');
            $table->timestamp('next_retry_at')->nullable()->after('retry_count');
            $table->unsignedBigInteger('created_by')->nullable()->after('next_retry_at');

            $table->index(['type', 'status']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index(['channel', 'status']);
            $table->index('scheduled_at');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['recipient_type', 'recipient_id']);
            $table->dropIndex(['channel', 'status']);
            $table->dropIndex(['scheduled_at']);
            $table->dropIndex(['created_by']);
            
            $table->dropColumn([
                'channel', 'title', 'message', 'recipient_type', 'recipient_id',
                'recipient_email', 'recipient_phone', 'status', 'scheduled_at',
                'sent_at', 'delivered_at', 'external_id', 'error_message',
                'retry_count', 'next_retry_at', 'created_by'
            ]);
        });
    }
};

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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable()->after('two_factor_confirmed_at');
            $table->integer('failed_login_attempts')->default(0)->after('password_changed_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->string('preferred_language', 5)->default('en')->after('locked_until');
            $table->json('notification_preferences')->nullable()->after('preferred_language');
            $table->boolean('email_notifications')->default(true)->after('notification_preferences');
            $table->boolean('sms_notifications')->default(false)->after('email_notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'password_changed_at',
                'failed_login_attempts',
                'locked_until',
                'preferred_language',
                'notification_preferences',
                'email_notifications',
                'sms_notifications'
            ]);
        });
    }
};
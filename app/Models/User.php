<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'avatar',
        'is_active',
        'last_login_at',
        'two_factor_enabled',
        'password_changed_at',
        'failed_login_attempts',
        'locked_until',
        'preferred_language',
        'notification_preferences',
        'email_notifications',
        'sms_notifications',
        'google2fa_secret',
        'google2fa_enabled_at',
        'backup_codes',
        'last_2fa_verified_at',
        'failed_2fa_attempts',
        '2fa_locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'google2fa_secret',
        'backup_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'password_changed_at' => 'datetime',
            'locked_until' => 'datetime',
            'notification_preferences' => 'array',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'google2fa_enabled_at' => 'datetime',
            'backup_codes' => 'array',
            'last_2fa_verified_at' => 'datetime',
            '2fa_locked_until' => 'datetime',
        ];
    }

    /**
     * Get the investor profile associated with the user.
     */
    public function investor()
    {
        return $this->hasOne(Investor::class);
    }

    /**
     * Get the orders created by the user.
     */
    public function createdOrders()
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    /**
     * Get the approvals made by the user.
     */
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    /**
     * Get the transactions created by the user.
     */
    public function createdTransactions()
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    /**
     * Get the inquiries assigned to the user.
     */
    public function assignedInquiries()
    {
        return $this->hasMany(Inquiry::class, 'assigned_to');
    }

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the investment statements created by the user.
     */
    public function createdStatements()
    {
        return $this->hasMany(InvestmentStatement::class, 'created_by');
    }

    /**
     * Get the investment statements approved by the user.
     */
    public function approvedStatements()
    {
        return $this->hasMany(InvestmentStatement::class, 'approved_by');
    }

    /**
     * Get the money receipts created by the user.
     */
    public function createdReceipts()
    {
        return $this->hasMany(MoneyReceipt::class, 'created_by');
    }

    /**
     * Get the money receipts verified by the user.
     */
    public function verifiedReceipts()
    {
        return $this->hasMany(MoneyReceipt::class, 'verified_by');
    }

    /**
     * Check if user is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->google2fa_secret;
    }

    /**
     * Check if user is 2FA locked.
     */
    public function is2FALocked(): bool
    {
        return $this->{'2fa_locked_until'} && $this->{'2fa_locked_until'}->isFuture();
    }

    /**
     * Generate backup codes for 2FA.
     */
    public function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid()), 0, 8));
        }
        return $codes;
    }

    /**
     * Use a backup code.
     */
    public function useBackupCode(string $code): bool
    {
        $backupCodes = $this->backup_codes ?? [];
        $key = array_search($code, $backupCodes);
        
        if ($key !== false) {
            unset($backupCodes[$key]);
            $this->update(['backup_codes' => array_values($backupCodes)]);
            return true;
        }
        
        return false;
    }
}

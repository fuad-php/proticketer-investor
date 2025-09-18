<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoneyReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'investor_id',
        'transaction_id',
        'investment_id',
        'receipt_type',
        'amount',
        'payment_method',
        'reference_number',
        'receipt_date',
        'description',
        'pdf_path',
        'digital_signature_path',
        'company_stamp_path',
        'is_verified',
        'verified_by',
        'verified_at',
        'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'amount' => 'decimal:2',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the investor that owns the receipt.
     */
    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    /**
     * Get the transaction associated with the receipt.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the investment associated with the receipt.
     */
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    /**
     * Get the user who created the receipt.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who verified the receipt.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope for verified receipts.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for unverified receipts.
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope for investment receipts.
     */
    public function scopeInvestment($query)
    {
        return $query->where('receipt_type', 'investment');
    }

    /**
     * Scope for profit payout receipts.
     */
    public function scopeProfitPayout($query)
    {
        return $query->where('receipt_type', 'profit_payout');
    }

    /**
     * Generate receipt number.
     */
    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastReceipt = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastReceipt ? (int) substr($lastReceipt->receipt_number, -4) + 1 : 1;

        return "RCPT-{$year}{$month}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
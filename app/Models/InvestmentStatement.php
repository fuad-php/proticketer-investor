<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'statement_number',
        'investor_id',
        'order_id',
        'investment_id',
        'statement_date',
        'period_start',
        'period_end',
        'opening_balance',
        'closing_balance',
        'total_investments',
        'total_profits',
        'total_payouts',
        'management_fees',
        'performance_fees',
        'transaction_summary',
        'status',
        'pdf_path',
        'digital_signature_path',
        'created_by',
        'approved_by',
        'approved_at',
        'published_at',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'total_investments' => 'decimal:2',
        'total_profits' => 'decimal:2',
        'total_payouts' => 'decimal:2',
        'management_fees' => 'decimal:2',
        'performance_fees' => 'decimal:2',
        'transaction_summary' => 'array',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Get the investor that owns the statement.
     */
    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    /**
     * Get the order associated with the statement.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the investment associated with the statement.
     */
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    /**
     * Get the user who created the statement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the statement.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for published statements.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for approved statements.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for draft statements.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Generate statement number.
     */
    public static function generateStatementNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastStatement = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastStatement ? (int) substr($lastStatement->statement_number, -4) + 1 : 1;

        return "STMT-{$year}{$month}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'investor_id',
        'amount',
        'profit_percentage',
        'expected_profit',
        'actual_profit',
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
        'last_payout_at',
        'investment_date',
        'maturity_date',
        'status',
        'payment_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'profit_percentage' => 'decimal:2',
            'expected_profit' => 'decimal:2',
            'actual_profit' => 'decimal:2',
            'management_fee' => 'decimal:2',
            'performance_fee' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'total_return' => 'decimal:2',
            'return_percentage' => 'decimal:2',
            'payout_amount' => 'decimal:2',
            'auto_reinvest' => 'boolean',
            'payout_history' => 'array',
            'investment_date' => 'date',
            'maturity_date' => 'date',
            'next_payout_date' => 'date',
            'last_payout_at' => 'datetime',
        ];
    }

    /**
     * Get the order that owns the investment.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the investor that owns the investment.
     */
    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    /**
     * Get the transactions for the investment.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the investment statements for the investment.
     */
    public function statements()
    {
        return $this->hasMany(InvestmentStatement::class);
    }

    /**
     * Get the money receipts for the investment.
     */
    public function receipts()
    {
        return $this->hasMany(MoneyReceipt::class);
    }

    /**
     * Scope for active investments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for matured investments.
     */
    public function scopeMatured($query)
    {
        return $query->where('status', 'matured');
    }

    /**
     * Scope for investments due for payout.
     */
    public function scopeDueForPayout($query)
    {
        return $query->where('next_payout_date', '<=', now())
            ->where('status', 'active');
    }

    /**
     * Calculate net profit after fees.
     */
    public function calculateNetProfit(): float
    {
        $grossProfit = $this->actual_profit ?? $this->expected_profit;
        return $grossProfit - $this->management_fee - $this->performance_fee;
    }

    /**
     * Calculate total return including principal.
     */
    public function calculateTotalReturn(): float
    {
        return $this->amount + $this->calculateNetProfit();
    }

    /**
     * Calculate return percentage.
     */
    public function calculateReturnPercentage(): float
    {
        if ($this->amount == 0) {
            return 0;
        }
        
        return ($this->calculateNetProfit() / $this->amount) * 100;
    }
}

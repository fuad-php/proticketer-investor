<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccountingLedger extends Model
{
    use HasFactory;

    protected $table = 'accounting_ledger';

    protected $fillable = [
        'transaction_id',
        'transaction_date',
        'transaction_type',
        'category',
        'description',
        'debit_amount',
        'credit_amount',
        'balance',
        'reference_type',
        'reference_id',
        'investor_id',
        'client_id',
        'payment_method',
        'reference_number',
        'notes',
        'metadata',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
        'reversal_reason',
        'reversed_by',
        'reversed_at',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'debit_amount' => 'decimal:2',
            'credit_amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'metadata' => 'array',
            'approved_at' => 'datetime',
            'reversed_at' => 'datetime',
        ];
    }

    // Relationships
    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeForInvestor($query, $investorId)
    {
        return $query->where('investor_id', $investorId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'reversed' => 'gray',
            default => 'gray'
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'reversed' => 'Reversed',
            default => 'Unknown'
        };
    }

    public function getTransactionTypeTextAttribute(): string
    {
        return match($this->transaction_type) {
            'investment' => 'Investment',
            'payout' => 'Payout',
            'fee' => 'Fee',
            'expense' => 'Expense',
            'revenue' => 'Revenue',
            'adjustment' => 'Adjustment',
            default => 'Other'
        };
    }

    public function getCategoryTextAttribute(): string
    {
        return match($this->category) {
            'investment_income' => 'Investment Income',
            'management_fee' => 'Management Fee',
            'performance_fee' => 'Performance Fee',
            'operating_expense' => 'Operating Expense',
            'payout' => 'Payout',
            'refund' => 'Refund',
            'adjustment' => 'Adjustment',
            default => 'Other'
        };
    }

    // Static methods
    public static function generateTransactionId(): string
    {
        return 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(8));
    }

    public static function getCurrentBalance(): float
    {
        $lastEntry = static::approved()->latest('transaction_date')->first();
        return $lastEntry ? $lastEntry->balance : 0;
    }

    public static function getBalanceForDate($date): float
    {
        $lastEntry = static::approved()
            ->where('transaction_date', '<=', $date)
            ->latest('transaction_date')
            ->first();
        return $lastEntry ? $lastEntry->balance : 0;
    }

    public static function getTotalByCategory($category, $startDate = null, $endDate = null): float
    {
        $query = static::approved()->byCategory($category);
        
        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }
        
        return $query->sum('credit_amount') - $query->sum('debit_amount');
    }

    public static function getTotalByType($type, $startDate = null, $endDate = null): float
    {
        $query = static::approved()->byType($type);
        
        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }
        
        return $query->sum('credit_amount') - $query->sum('debit_amount');
    }
}

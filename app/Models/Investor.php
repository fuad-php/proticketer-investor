<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Investor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'investor_code',
        'full_name',
        'email',
        'phone',
        'address',
        'nid_number',
        'bank_name',
        'bank_account',
        'bank_routing',
        'total_invested',
        'total_profit',
        'current_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_invested' => 'decimal:2',
            'total_profit' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the investor profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investments for the investor.
     */
    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    /**
     * Get the transactions for the investor.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the investment statements for the investor.
     */
    public function statements()
    {
        return $this->hasMany(InvestmentStatement::class);
    }

    /**
     * Get the money receipts for the investor.
     */
    public function receipts()
    {
        return $this->hasMany(MoneyReceipt::class);
    }

    /**
     * Get active investments.
     */
    public function activeInvestments()
    {
        return $this->investments()->where('status', 'active');
    }

    /**
     * Get matured investments.
     */
    public function maturedInvestments()
    {
        return $this->investments()->where('status', 'matured');
    }

    /**
     * Get total current value of all investments.
     */
    public function getTotalCurrentValueAttribute()
    {
        return $this->activeInvestments()->sum('amount') + $this->total_profit;
    }

    /**
     * Get total return percentage.
     */
    public function getTotalReturnPercentageAttribute()
    {
        if ($this->total_invested == 0) {
            return 0;
        }
        
        return ($this->total_profit / $this->total_invested) * 100;
    }
}

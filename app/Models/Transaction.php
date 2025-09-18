<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'investor_id',
        'investment_id',
        'order_id',
        'type',
        'entry_type',
        'amount',
        'balance_after',
        'description',
        'reference_number',
        'created_by',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'transaction_date' => 'datetime',
        ];
    }

    /**
     * Get the investor that owns the transaction.
     */
    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    /**
     * Get the investment that owns the transaction.
     */
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    /**
     * Get the order that owns the transaction.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who created the transaction.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

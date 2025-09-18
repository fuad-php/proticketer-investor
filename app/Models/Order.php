<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'client_id',
        'created_by',
        'title',
        'description',
        'product_category',
        'product_specs',
        'total_amount',
        'profit_percentage',
        'management_fee_percentage',
        'performance_fee_percentage',
        'risk_level',
        'investor_list',
        'start_date',
        'end_date',
        'status',
        'payment_status',
        'notes',
        'attachments',
        'supporting_documents',
        'statement_version',
        'is_published',
        'published_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'profit_percentage' => 'decimal:2',
            'management_fee_percentage' => 'decimal:2',
            'performance_fee_percentage' => 'decimal:2',
            'investor_list' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'attachments' => 'array',
            'supporting_documents' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the client that owns the order.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who created the order.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the investments for the order.
     */
    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    /**
     * Get the approvals for the order.
     */
    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    /**
     * Get the transactions for the order.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the investment statements for the order.
     */
    public function statements()
    {
        return $this->hasMany(InvestmentStatement::class);
    }

    /**
     * Get the user who published the order.
     */
    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Scope for published orders.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for draft orders.
     */
    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    /**
     * Scope for active orders.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if order is fully approved.
     */
    public function isFullyApproved(): bool
    {
        $requiredApprovals = ['director', 'managing_director', 'chairman'];
        $approvedRoles = $this->approvals()
            ->where('status', 'approved')
            ->pluck('approver_role')
            ->toArray();

        return count(array_intersect($requiredApprovals, $approvedRoles)) === count($requiredApprovals);
    }

    /**
     * Get next required approval role.
     */
    public function getNextApprovalRole(): ?string
    {
        $requiredApprovals = ['director', 'managing_director', 'chairman'];
        $approvedRoles = $this->approvals()
            ->where('status', 'approved')
            ->pluck('approver_role')
            ->toArray();

        foreach ($requiredApprovals as $role) {
            if (!in_array($role, $approvedRoles)) {
                return $role;
            }
        }

        return null;
    }
}

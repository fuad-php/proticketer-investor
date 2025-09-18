<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_number',
        'client_id',
        'subject',
        'description',
        'category',
        'quantity',
        'specifications',
        'preferred_timeframe',
        'status',
        'priority',
        'assigned_to',
        'response',
        'response_date',
        'quotation_amount',
        'quotation_valid_until',
        'attachments',
        'response_attachments',
        'internal_notes',
        'estimated_completion_date',
        'actual_completion_date',
    ];

    protected function casts(): array
    {
        return [
            'preferred_timeframe' => 'date',
            'response_date' => 'datetime',
            'quotation_valid_until' => 'date',
            'estimated_completion_date' => 'date',
            'actual_completion_date' => 'date',
            'attachments' => 'array',
            'response_attachments' => 'array',
            'quotation_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the client that owns the inquiry.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user assigned to the inquiry.
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope for received inquiries.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope for in progress inquiries.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for quoted inquiries.
     */
    public function scopeQuoted($query)
    {
        return $query->where('status', 'quoted');
    }

    /**
     * Scope for completed inquiries.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for closed inquiries.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for high priority inquiries.
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    /**
     * Scope for assigned inquiries.
     */
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_to');
    }

    /**
     * Scope for unassigned inquiries.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Check if inquiry is overdue.
     */
    public function isOverdue(): bool
    {
        if (!$this->preferred_timeframe) {
            return false;
        }
        
        return $this->preferred_timeframe->isPast() && !in_array($this->status, ['completed', 'closed']);
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'received' => 'blue',
            'in_progress' => 'yellow',
            'quoted' => 'purple',
            'completed' => 'green',
            'closed' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'red',
            'urgent' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get days since inquiry was created.
     */
    public function getDaysSinceCreatedAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Generate inquiry number.
     */
    public static function generateInquiryNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastInquiry = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInquiry ? (int) substr($lastInquiry->inquiry_number, -4) + 1 : 1;

        return "INQ-{$year}{$month}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

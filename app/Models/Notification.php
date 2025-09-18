<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'channel',
        'title',
        'message',
        'data',
        'recipient_type',
        'recipient_id',
        'recipient_email',
        'recipient_phone',
        'status',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'external_id',
        'error_message',
        'retry_count',
        'next_retry_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    // Relationships
    public function recipient()
    {
        return $this->morphTo('recipient', 'recipient_type', 'recipient_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeScheduled($query)
    {
        return $query->where('scheduled_at', '<=', now());
    }

    public function scopeForRecipient($query, $recipientType, $recipientId)
    {
        return $query->where('recipient_type', $recipientType)
                    ->where('recipient_id', $recipientId);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    public function markAsSent(string $externalId = null): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'external_id' => $externalId,
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
            'next_retry_at' => now()->addMinutes(pow(2, $this->retry_count)), // Exponential backoff
        ]);
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function canRetry(): bool
    {
        return $this->retry_count < 5 && $this->next_retry_at && $this->next_retry_at->isPast();
    }

    // Static methods
    public static function createForUser(User $user, string $type, string $channel, string $title, string $message, array $data = []): self
    {
        return static::create([
            'type' => $type,
            'channel' => $channel,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'recipient_type' => 'user',
            'recipient_id' => $user->id,
            'recipient_email' => $user->email,
            'recipient_phone' => $user->phone,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);
    }

    public static function createForInvestor(Investor $investor, string $type, string $channel, string $title, string $message, array $data = []): self
    {
        return static::create([
            'type' => $type,
            'channel' => $channel,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'recipient_type' => 'investor',
            'recipient_id' => $investor->id,
            'recipient_email' => $investor->email,
            'recipient_phone' => $investor->phone,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);
    }

    public static function createForClient(Client $client, string $type, string $channel, string $title, string $message, array $data = []): self
    {
        return static::create([
            'type' => $type,
            'channel' => $channel,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'recipient_type' => 'client',
            'recipient_id' => $client->id,
            'recipient_email' => $client->email,
            'recipient_phone' => $client->phone,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);
    }

    public static function createForRole(string $role, string $type, string $channel, string $title, string $message, array $data = []): self
    {
        return static::create([
            'type' => $type,
            'channel' => $channel,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'recipient_type' => 'role',
            'recipient_id' => null,
            'recipient_email' => null,
            'recipient_phone' => null,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);
    }

    public static function createScheduled(\DateTime $scheduledAt, string $type, string $channel, string $title, string $message, array $data = []): self
    {
        return static::create([
            'type' => $type,
            'channel' => $channel,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'recipient_type' => 'user',
            'recipient_id' => auth()->id(),
            'recipient_email' => auth()->user()->email,
            'recipient_phone' => auth()->user()->phone,
            'status' => 'pending',
            'scheduled_at' => $scheduledAt,
            'created_by' => auth()->id(),
        ]);
    }
}

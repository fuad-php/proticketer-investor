<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'title',
        'description',
        'type',
        'category',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'hash',
        'status',
        'visibility',
        'metadata',
        'tags',
        'reference_type',
        'reference_id',
        'uploaded_by',
        'approved_by',
        'approved_at',
        'expires_at',
        'download_count',
        'last_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'tags' => 'array',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    // Relationships
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeDeleted($query)
    {
        return $query->where('status', 'deleted');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    public function scopeRestricted($query)
    {
        return $query->where('visibility', 'restricted');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForReference($query, string $type, $id)
    {
        return $query->where('reference_type', $type)
                    ->where('reference_id', $id);
    }

    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isDeleted(): bool
    {
        return $this->status === 'deleted';
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function isRestricted(): bool
    {
        return $this->visibility === 'restricted';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('documents.download', $this->id);
    }

    public function getPreviewUrlAttribute(): string
    {
        return route('documents.preview', $this->id);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'archived' => 'warning',
            'deleted' => 'danger',
            default => 'secondary'
        };
    }

    public function getVisibilityBadgeColorAttribute(): string
    {
        return match($this->visibility) {
            'public' => 'success',
            'private' => 'warning',
            'restricted' => 'danger',
            default => 'secondary'
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->file_type) {
            'pdf' => 'fas fa-file-pdf',
            'doc', 'docx' => 'fas fa-file-word',
            'xls', 'xlsx' => 'fas fa-file-excel',
            'ppt', 'pptx' => 'fas fa-file-powerpoint',
            'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image',
            'txt' => 'fas fa-file-alt',
            'zip', 'rar' => 'fas fa-file-archive',
            default => 'fas fa-file'
        };
    }

    public function canBeDownloadedBy(User $user): bool
    {
        // Check if document is active and not expired
        if (!$this->isActive() || $this->isExpired()) {
            return false;
        }

        // Check visibility permissions
        switch ($this->visibility) {
            case 'public':
                return true;
            case 'private':
                return $user->id === $this->uploaded_by || $user->hasRole('super_admin');
            case 'restricted':
                return $user->hasRole(['super_admin', 'director', 'managing_director']);
            default:
                return false;
        }
    }

    public function canBeViewedBy(User $user): bool
    {
        return $this->canBeDownloadedBy($user);
    }

    public function canBeEditedBy(User $user): bool
    {
        return $user->id === $this->uploaded_by || $user->hasRole('super_admin');
    }

    public function canBeDeletedBy(User $user): bool
    {
        return $user->id === $this->uploaded_by || $user->hasRole('super_admin');
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function restore(): void
    {
        $this->update(['status' => 'active']);
    }

    public function softDelete(): void
    {
        $this->update(['status' => 'deleted']);
    }

    public function approve(User $user): void
    {
        $this->update([
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $tags = array_filter($tags, fn($t) => $t !== $tag);
        $this->update(['tags' => array_values($tags)]);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    // Static methods
    public static function generateDocumentNumber(): string
    {
        return 'DOC-' . date('Ymd') . '-' . strtoupper(Str::random(8));
    }

    public static function getStoragePath(string $type, string $category): string
    {
        return "documents/{$type}/{$category}/" . date('Y/m');
    }

    public static function createFromFile(
        string $filePath,
        string $title,
        string $type,
        string $category,
        User $uploadedBy,
        array $options = []
    ): self {
        $fileInfo = pathinfo($filePath);
        $fileSize = filesize($filePath);
        $fileHash = hash_file('sha256', $filePath);
        
        $document = static::create([
            'document_number' => static::generateDocumentNumber(),
            'title' => $title,
            'description' => $options['description'] ?? null,
            'type' => $type,
            'category' => $category,
            'file_name' => $fileInfo['basename'],
            'file_path' => $filePath,
            'file_type' => strtolower($fileInfo['extension']),
            'file_size' => $fileSize,
            'mime_type' => mime_content_type($filePath),
            'hash' => $fileHash,
            'status' => $options['status'] ?? 'active',
            'visibility' => $options['visibility'] ?? 'private',
            'metadata' => $options['metadata'] ?? null,
            'tags' => $options['tags'] ?? null,
            'reference_type' => $options['reference_type'] ?? null,
            'reference_id' => $options['reference_id'] ?? null,
            'uploaded_by' => $uploadedBy->id,
            'expires_at' => $options['expires_at'] ?? null,
        ]);

        return $document;
    }

    public static function getTotalSizeByType(string $type): int
    {
        return static::where('type', $type)
            ->where('status', 'active')
            ->sum('file_size');
    }

    public static function getTotalSizeByCategory(string $category): int
    {
        return static::where('category', $category)
            ->where('status', 'active')
            ->sum('file_size');
    }

    public static function getStorageStats(): array
    {
        $totalSize = static::where('status', 'active')->sum('file_size');
        $totalFiles = static::where('status', 'active')->count();
        
        $byType = static::where('status', 'active')
            ->selectRaw('type, COUNT(*) as count, SUM(file_size) as size')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $byCategory = static::where('status', 'active')
            ->selectRaw('category, COUNT(*) as count, SUM(file_size) as size')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        return [
            'total_size' => $totalSize,
            'total_files' => $totalFiles,
            'by_type' => $byType,
            'by_category' => $byCategory,
        ];
    }
}

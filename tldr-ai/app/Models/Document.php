<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'summary',
        'summary_generated',
        'public_url',
    ];

    protected $casts = [
        'summary_generated' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the user that owns the document
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) return 'Unknown';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->file_size;
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Get display summary (with fallback)
     */
    public function getDisplaySummaryAttribute(): string
    {
        if ($this->summary_generated && $this->summary) {
            return $this->summary;
        }
        
        return 'Click to generate summary';
    }
}
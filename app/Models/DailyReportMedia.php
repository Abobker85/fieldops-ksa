<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DailyReportMedia extends Model
{
    use HasFactory;

    protected $table = 'daily_report_media';

    protected $fillable = [
        'daily_report_id',
        'file_path',
        'thumbnail_path',
        'file_type',
        'caption',
        'geo_latitude',
        'geo_longitude',
        'captured_at',
    ];

    protected $casts = [
        'geo_latitude' => 'decimal:8',
        'geo_longitude' => 'decimal:8',
        'captured_at' => 'datetime',
    ];

    protected $appends = [
        'file_url',
        'thumbnail_url',
    ];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function getFileUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }
        return Storage::disk('public')->url($this->file_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (!$this->thumbnail_path) {
            return $this->file_url;
        }
        if (str_starts_with($this->thumbnail_path, 'http://') || str_starts_with($this->thumbnail_path, 'https://')) {
            return $this->thumbnail_path;
        }
        return Storage::disk('public')->url($this->thumbnail_path);
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'user_id',
        'report_date',
        'weather_condition',
        'manpower_count',
        'work_summary',
        'blockers_notes',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
        'manpower_count' => 'integer',
    ];

    protected $appends = [
        'share_token',
        'export_url',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(DailyReportMedia::class);
    }

    public function getShareTokenAttribute(): string
    {
        return hash_hmac('sha256', "report-{$this->id}-{$this->project_id}", config('app.key') ?: 'fieldops-secret-key');
    }

    public function getExportUrlAttribute(): string
    {
        return url("/api/v1/daily-reports/{$this->id}/export-pdf?share_token={$this->share_token}");
    }
}

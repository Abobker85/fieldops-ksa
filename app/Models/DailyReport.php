<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
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
}

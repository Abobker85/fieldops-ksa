<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'client_name',
        'consultant_name',
        'location_city',
        'location_coordinates',
        'contract_value',
        'start_date',
        'expected_end_date',
        'status',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'start_date' => 'date',
        'expected_end_date' => 'date',
    ];

    protected $appends = [
        'weighted_progress',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class)->orderBy('report_date', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class)->orderBy('created_at', 'desc');
    }

    public function siteRequests(): HasMany
    {
        return $this->hasMany(SiteRequest::class)->orderBy('created_at', 'desc');
    }

    public function boqItems(): HasMany
    {
        return $this->hasMany(BoqItem::class);
    }

    public function paymentClaims(): HasMany
    {
        return $this->hasMany(PaymentClaim::class)->orderBy('period_end', 'desc');
    }

    /**
     * Calculate overall weighted progress percentage based on BOQ items.
     * Progress = Sum( (weight_percentage * current_progress_percentage) / 100 )
     */
    public function getWeightedProgressAttribute(): float
    {
        $items = $this->relationLoaded('boqItems') ? $this->boqItems : $this->boqItems()->get();
        if ($items->isEmpty()) {
            return 0.0;
        }

        $totalWeightedProgress = 0.0;
        foreach ($items as $item) {
            $totalWeightedProgress += ($item->weight_percentage * $item->current_progress_percentage) / 100.0;
        }

        return round(min(100.0, max(0.0, $totalWeightedProgress)), 2);
    }
}

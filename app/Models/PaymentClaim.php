<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentClaim extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'claim_number',
        'period_start',
        'period_end',
        'claimed_amount',
        'approved_amount',
        'vat_amount',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'claimed_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    protected $appends = [
        'total_claimed_with_vat',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getTotalClaimedWithVatAttribute(): float
    {
        return round((float) $this->claimed_amount + (float) $this->vat_amount, 2);
    }
}

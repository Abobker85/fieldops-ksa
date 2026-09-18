<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'requested_by',
        'type',
        'request_number',
        'title',
        'description',
        'location_details',
        'estimated_cost_impact',
        'status',
        'response_notes',
    ];

    protected $casts = [
        'estimated_cost_impact' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}

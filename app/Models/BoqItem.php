<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoqItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'item_code',
        'description',
        'unit',
        'total_quantity',
        'unit_price',
        'total_price',
        'weight_percentage',
        'current_progress_percentage',
    ];

    protected $casts = [
        'total_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'weight_percentage' => 'decimal:2',
        'current_progress_percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (BoqItem $item) {
            if (empty($item->total_price) && $item->total_quantity !== null && $item->unit_price !== null) {
                $item->total_price = round($item->total_quantity * $item->unit_price, 2);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

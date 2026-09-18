<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', auth()->user()->tenant_id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                if (auth()->check() && auth()->user()->tenant_id) {
                    $model->tenant_id = auth()->user()->tenant_id;
                } elseif (!empty($model->project_id)) {
                    $project = \App\Models\Project::withoutGlobalScopes()->find($model->project_id);
                    if ($project) {
                        $model->tenant_id = $project->tenant_id;
                    }
                } elseif (!empty($model->daily_report_id)) {
                    $report = \App\Models\DailyReport::withoutGlobalScopes()->find($model->daily_report_id);
                    if ($report) {
                        $model->tenant_id = $report->tenant_id;
                    }
                }
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

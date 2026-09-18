<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::with(['boqItems'])
            ->withCount(['dailyReports', 'siteRequests', 'documents'])
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponse::success($projects, 'قائمة المشاريع');
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->roles()->exists() && !$user->hasAnyRole(['owner', 'pm'])) {
            return ApiResponse::error('غير مصرح لك بإنشاء مشاريع جديدة', 403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:191'],
            'client_name' => ['required', 'string', 'max:191'],
            'consultant_name' => ['nullable', 'string', 'max:191'],
            'location_city' => ['required', 'string', 'max:100'],
            'location_coordinates' => ['nullable', 'string', 'max:100'],
            'contract_value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'expected_end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:active,completed,on_hold'],
        ]);

        $project = Project::create($validated);
        $project->load('boqItems');

        return ApiResponse::success($project, 'تم إنشاء المشروع بنجاح', 201);
    }

    public function show(int $id): JsonResponse
    {
        $project = Project::with([
            'boqItems',
            'dailyReports.media',
            'dailyReports.user',
            'documents.uploader',
            'siteRequests.requester',
            'paymentClaims',
        ])->findOrFail($id);

        return ApiResponse::success($project, 'تفاصيل المشروع');
    }

    public function executiveSummary(int $id): JsonResponse
    {
        $project = Project::with(['boqItems', 'paymentClaims', 'dailyReports' => function ($q) {
            $q->orderBy('report_date', 'desc')->take(5);
        }])->findOrFail($id);

        $today = Carbon::today()->toDateString();
        $latestReport = $project->dailyReports->first();

        $openRequestsCount = $project->siteRequests()->whereIn('status', ['pending', 'under_review'])->count();
        $totalClaimed = (float) $project->paymentClaims->sum('claimed_amount');
        $totalApproved = (float) $project->paymentClaims->sum('approved_amount');

        $summary = [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'project_code' => $project->code,
            'contract_value' => (float) $project->contract_value,
            'weighted_progress' => $project->weighted_progress,
            'total_claimed' => $totalClaimed,
            'total_approved_claims' => $totalApproved,
            'today_or_latest_manpower' => $latestReport ? $latestReport->manpower_count : 0,
            'open_requests_count' => $openRequestsCount,
            'latest_report' => $latestReport ? [
                'id' => $latestReport->id,
                'report_date' => $latestReport->report_date->toDateString(),
                'manpower_count' => $latestReport->manpower_count,
                'weather_condition' => $latestReport->weather_condition,
                'work_summary' => $latestReport->work_summary,
                'blockers_notes' => $latestReport->blockers_notes,
                'status' => $latestReport->status,
            ] : null,
        ];

        return ApiResponse::success($summary, 'الملخص التنفيذي للمشروع');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SiteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteRequestController extends Controller
{
    public function index(Request $request, int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);

        $query = $project->siteRequests()->with('requester');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->get();

        return ApiResponse::success($requests, 'قائمة الطلبات الهندسية');
    }

    public function store(Request $request, int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'type' => ['required', 'in:RFI,WIR,VARIATION_ORDER'],
            'title' => ['required', 'string', 'max:191'],
            'description' => ['required', 'string'],
            'location_details' => ['nullable', 'string', 'max:191'],
            'estimated_cost_impact' => ['nullable', 'numeric', 'min:0'],
            'request_number' => ['nullable', 'string', 'max:50'],
        ]);

        $typePrefix = match ($validated['type']) {
            'WIR' => 'WIR',
            'VARIATION_ORDER' => 'VO',
            default => 'RFI',
        };

        if (empty($validated['request_number'])) {
            $count = SiteRequest::where('project_id', $project->id)
                ->where('type', $validated['type'])
                ->count() + 1;
            $requestNumber = sprintf('%s-%03d', $typePrefix, $count);
        } else {
            $requestNumber = $validated['request_number'];
        }

        $siteRequest = SiteRequest::create([
            'project_id' => $project->id,
            'requested_by' => $request->user()->id,
            'type' => $validated['type'],
            'request_number' => $requestNumber,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location_details' => $validated['location_details'] ?? null,
            'estimated_cost_impact' => $validated['estimated_cost_impact'] ?? 0.00,
            'status' => 'pending',
        ]);

        $siteRequest->load('requester');

        return ApiResponse::success($siteRequest, 'تم تقديم الطلب الهندسي بنجاح', 201);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $siteRequest = SiteRequest::with(['project', 'requester'])->findOrFail($id);

        $user = $request->user();
        if ($user->roles()->exists() && !$user->hasAnyRole(['owner', 'pm', 'viewer'])) {
            return ApiResponse::error('غير مصرح لك باعتماد أو تعديل حالة الطلب الهندسي', 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,under_review'],
            'response_notes' => ['nullable', 'string'],
        ]);

        $siteRequest->update([
            'status' => $validated['status'],
            'response_notes' => $validated['response_notes'] ?? $siteRequest->response_notes,
        ]);

        return ApiResponse::success($siteRequest, 'تم تحديث حالة الطلب بنجاح');
    }
}

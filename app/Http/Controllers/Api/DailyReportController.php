<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessDailyReportMediaThumbnail;
use App\Models\DailyReport;
use App\Models\DailyReportMedia;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DailyReportController extends Controller
{
    public function index(Request $request, int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);

        $query = $project->dailyReports()->with(['media', 'user']);

        if ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->get();

        return ApiResponse::success($reports, 'سجل التقارير اليومية');
    }

    public function store(Request $request, int $projectId): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->roles()->exists() && !$user->hasAnyRole(['owner', 'pm', 'site_engineer'])) {
            return ApiResponse::error('غير مصرح لك بتسجيل التقارير اليومية', 403);
        }

        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'report_date' => ['required', 'date'],
            'weather_condition' => ['nullable', 'string', 'max:50'],
            'manpower_count' => ['required', 'integer', 'min:0'],
            'work_summary' => ['required', 'string'],
            'blockers_notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,submitted,approved'],
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $project, $validated) {
            $existing = DailyReport::where('project_id', $project->id)
                ->whereDate('report_date', $validated['report_date'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->update([
                    'weather_condition' => $validated['weather_condition'] ?? $existing->weather_condition,
                    'manpower_count' => $validated['manpower_count'],
                    'work_summary' => $validated['work_summary'],
                    'blockers_notes' => $validated['blockers_notes'] ?? null,
                    'status' => $validated['status'] ?? 'submitted',
                ]);
                $report = $existing->load(['media', 'user']);
                return ApiResponse::success($report, 'تم تحديث التقرير اليومي بنجاح');
            }

            $report = DailyReport::create([
                'tenant_id' => $project->tenant_id,
                'project_id' => $project->id,
                'user_id' => $request->user()->id,
                'report_date' => $validated['report_date'],
                'weather_condition' => $validated['weather_condition'] ?? 'مشمس / معتدل',
                'manpower_count' => $validated['manpower_count'],
                'work_summary' => $validated['work_summary'],
                'blockers_notes' => $validated['blockers_notes'] ?? null,
                'status' => $validated['status'] ?? 'submitted',
            ]);

            $report->load(['media', 'user']);

            return ApiResponse::success($report, 'تم حفظ التقرير اليومي بنجاح', 201);
        });
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $report = DailyReport::with(['project', 'user'])->findOrFail($id);

        $user = $request->user();
        if ($user->roles()->exists() && !$user->hasAnyRole(['owner', 'pm'])) {
            return ApiResponse::error('غير مصرح لك باعتماد أو تعديل حالة التقرير اليومي', 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:draft,submitted,approved'],
        ]);

        $report->update(['status' => $validated['status']]);
        $report->load(['media', 'user']);

        return ApiResponse::success($report, 'تم تحديث حالة التقرير اليومي بنجاح');
    }

    public function show(int $id): JsonResponse
    {
        $report = DailyReport::with(['project.tenant', 'user', 'media'])->findOrFail($id);

        return ApiResponse::success($report, 'تفاصيل التقرير اليومي');
    }

    public function uploadMedia(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->roles()->exists() && !$user->hasAnyRole(['owner', 'pm', 'site_engineer'])) {
            return ApiResponse::error('غير مصرح لك برفع وسائط التقرير اليومي', 403);
        }

        $report = DailyReport::with('project')->findOrFail($id);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpeg,png,jpg,webp,mp4,mov', 'max:20480'], // max 20MB
            'caption' => ['nullable', 'string', 'max:191'],
            'geo_latitude' => ['nullable', 'numeric'],
            'geo_longitude' => ['nullable', 'numeric'],
            'captured_at' => ['nullable', 'date'],
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();
        $isImage = str_starts_with($mime, 'image/');

        $storedPath = $file->store("projects/{$report->project_id}/reports/{$report->id}", 'public');

        $media = DailyReportMedia::create([
            'tenant_id' => $report->tenant_id,
            'daily_report_id' => $report->id,
            'file_path' => $storedPath,
            'file_type' => $isImage ? 'image' : 'video',
            'caption' => $request->input('caption'),
            'geo_latitude' => $request->input('geo_latitude'),
            'geo_longitude' => $request->input('geo_longitude'),
            'captured_at' => $request->input('captured_at') ? now()->parse($request->input('captured_at')) : now(),
        ]);

        if ($isImage) {
            // Process thumbnail synchronously or via queue
            ProcessDailyReportMediaThumbnail::dispatchSync($media);
            $media->refresh();
        }

        return ApiResponse::success($media, 'تم رفع الوسائط بنجاح', 201);
    }

    public function exportPdf(Request $request, int $id)
    {
        $shareToken = $request->query('share_token');

        if ($shareToken) {
            $report = DailyReport::withoutGlobalScopes()
                ->with(['project.tenant', 'user', 'media'])
                ->findOrFail($id);

            $expectedToken = hash_hmac('sha256', "report-{$report->id}-{$report->project_id}", config('app.key') ?: 'fieldops-secret-key');
            if (!hash_equals($expectedToken, $shareToken)) {
                abort(403, 'رابط مشاركة التقرير غير صالح أو منتهي');
            }
        } else {
            $user = $request->user('sanctum') ?? auth()->user();
            if (!$user && $request->filled('token')) {
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($request->query('token'));
                if ($tokenModel) {
                    $user = $tokenModel->tokenable;
                }
            }

            if (!$user) {
                abort(401, 'يرجى تسجيل الدخول لعرض التقرير');
            }

            $report = DailyReport::withoutGlobalScopes()
                ->where('tenant_id', $user->tenant_id)
                ->with(['project.tenant', 'user', 'media'])
                ->findOrFail($id);
        }

        $pdf = Pdf::loadView('reports.daily-pdf', [
            'report' => $report,
            'project' => $report->project,
            'tenant' => $report->project->tenant,
            'media' => $report->media,
        ]);

        $filename = "Daily-Report-{$report->project->code}-{$report->report_date->format('Y-m-d')}.pdf";

        return $pdf->download($filename);
    }
}

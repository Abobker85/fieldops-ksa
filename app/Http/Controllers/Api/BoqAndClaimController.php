<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\BoqItem;
use App\Models\PaymentClaim;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoqAndClaimController extends Controller
{
    public function getBoq(int $projectId): JsonResponse
    {
        $project = Project::with('boqItems')->findOrFail($projectId);

        return ApiResponse::success([
            'items' => $project->boqItems,
            'weighted_progress' => $project->weighted_progress,
        ], 'بنود جدول الكميات والمقايسة');
    }

    public function storeBoqItem(Request $request, int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'item_code' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string'],
            'unit' => ['required', 'string', 'max:20'],
            'total_quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'weight_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'current_progress_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $totalPrice = round($validated['total_quantity'] * $validated['unit_price'], 2);

        $item = BoqItem::create([
            'project_id' => $project->id,
            'item_code' => $validated['item_code'],
            'description' => $validated['description'],
            'unit' => $validated['unit'],
            'total_quantity' => $validated['total_quantity'],
            'unit_price' => $validated['unit_price'],
            'total_price' => $totalPrice,
            'weight_percentage' => $validated['weight_percentage'],
            'current_progress_percentage' => $validated['current_progress_percentage'] ?? 0.00,
        ]);

        $project->refresh();

        return ApiResponse::success([
            'item' => $item,
            'project_weighted_progress' => $project->weighted_progress,
        ], 'تم إضافة بند المقايسة بنجاح', 201);
    }

    public function updateBoqProgress(Request $request, int $id): JsonResponse
    {
        $item = BoqItem::with('project')->findOrFail($id);

        $validated = $request->validate([
            'current_progress_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $item->update([
            'current_progress_percentage' => $validated['current_progress_percentage'],
        ]);

        $item->project->refresh();

        return ApiResponse::success([
            'item' => $item,
            'project_weighted_progress' => $item->project->weighted_progress,
        ], 'تم تحديث نسبة إنجاز البند وإعادة احتساب نسبة المشروع');
    }

    public function getClaims(int $projectId): JsonResponse
    {
        $project = Project::with('paymentClaims')->findOrFail($projectId);

        return ApiResponse::success($project->paymentClaims, 'مستخلصات المشروع');
    }

    public function storeClaim(Request $request, int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'claim_number' => ['required', 'string', 'max:50'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'claimed_amount' => ['required', 'numeric', 'min:0'],
            'vat_amount' => ['nullable', 'numeric', 'min:0'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:submitted,certified,paid_partially,paid'],
        ]);

        $vatAmount = $validated['vat_amount'] ?? round($validated['claimed_amount'] * 0.15, 2);

        $claim = PaymentClaim::create([
            'project_id' => $project->id,
            'claim_number' => $validated['claim_number'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'claimed_amount' => $validated['claimed_amount'],
            'approved_amount' => $validated['approved_amount'] ?? null,
            'vat_amount' => $vatAmount,
            'status' => $validated['status'] ?? 'submitted',
        ]);

        return ApiResponse::success($claim, 'تم تسجيل المستخلص بنجاح', 201);
    }
}

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoqAndClaimController;
use App\Http\Controllers\Api\DailyReportController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SiteRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);

        // Projects
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{id}', [ProjectController::class, 'show']);
        Route::get('/projects/{id}/executive-summary', [ProjectController::class, 'executiveSummary']);

        // Daily Reports & Media
        Route::get('/projects/{projectId}/daily-reports', [DailyReportController::class, 'index']);
        Route::post('/projects/{projectId}/daily-reports', [DailyReportController::class, 'store']);
        Route::get('/daily-reports/{id}', [DailyReportController::class, 'show']);
        Route::post('/daily-reports/{id}/media', [DailyReportController::class, 'uploadMedia']);
        Route::get('/daily-reports/{id}/export-pdf', [DailyReportController::class, 'exportPdf']);

        // Documents & Drawings Vault
        Route::get('/projects/{projectId}/documents', [DocumentController::class, 'index']);
        Route::post('/projects/{projectId}/documents', [DocumentController::class, 'store']);

        // Site Requests (WIR, RFI, Variation Orders)
        Route::get('/projects/{projectId}/requests', [SiteRequestController::class, 'index']);
        Route::post('/projects/{projectId}/requests', [SiteRequestController::class, 'store']);
        Route::patch('/requests/{id}/status', [SiteRequestController::class, 'updateStatus']);

        // BOQ Items & Payment Claims
        Route::get('/projects/{projectId}/boq', [BoqAndClaimController::class, 'getBoq']);
        Route::post('/projects/{projectId}/boq', [BoqAndClaimController::class, 'storeBoqItem']);
        Route::patch('/boq/{id}/progress', [BoqAndClaimController::class, 'updateBoqProgress']);
        Route::get('/projects/{projectId}/claims', [BoqAndClaimController::class, 'getClaims']);
        Route::post('/projects/{projectId}/claims', [BoqAndClaimController::class, 'storeClaim']);
    });
});

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request, int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);

        $query = $project->documents()->with('uploader');

        if ($request->filled('discipline')) {
            $query->where('discipline', $request->discipline);
        }

        if ($request->has('ifc_only') && $request->boolean('ifc_only')) {
            $query->where('is_approved_for_construction', true);
        }

        $documents = $query->get();

        return ApiResponse::success($documents, 'قائمة المخططات والمستندات');
    }

    public function store(Request $request, int $projectId): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->roles()->exists() && !$user->hasAnyRole(['owner', 'pm', 'site_engineer'])) {
            return ApiResponse::error('غير مصرح لك برفع المخططات والمستندات', 403);
        }

        $project = Project::findOrFail($projectId);

        $isIfc = $request->boolean('is_approved_for_construction');
        if ($isIfc && $user && $user->roles()->exists() && !$user->hasAnyRole(['owner', 'pm'])) {
            return ApiResponse::error('غير مصرح لك باعتماد المخططات للبناء (IFC)', 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'document_code' => ['required', 'string', 'max:100'],
            'discipline' => ['required', 'in:architectural,structural,mechanical,electrical,contracts,permits'],
            'current_revision' => ['nullable', 'string', 'max:10'],
            'is_approved_for_construction' => ['nullable', 'boolean'],
            'file' => ['required', 'file', 'mimes:pdf,dwg,dxf,png,jpg,jpeg,zip', 'max:51200'], // max 50MB
        ]);

        $currentRevision = $validated['current_revision'] ?? null;
        if (empty($currentRevision)) {
            $existingRevisions = ProjectDocument::where('project_id', $project->id)
                ->where('document_code', $validated['document_code'])
                ->pluck('current_revision');

            $maxRev = -1;
            foreach ($existingRevisions as $rev) {
                if ($rev && preg_match('/Rev\s*(\d+)/i', $rev, $matches)) {
                    $maxRev = max($maxRev, intval($matches[1]));
                }
            }
            $currentRevision = ($maxRev >= 0) ? sprintf('Rev %02d', $maxRev + 1) : 'Rev 00';
        }

        if ($isIfc) {
            ProjectDocument::where('project_id', $project->id)
                ->where('document_code', $validated['document_code'])
                ->update(['is_approved_for_construction' => false]);
        }

        $filePath = $request->file('file')->store("projects/{$project->id}/documents", 'public');

        $document = ProjectDocument::create([
            'tenant_id' => $project->tenant_id,
            'project_id' => $project->id,
            'title' => $validated['title'],
            'document_code' => $validated['document_code'],
            'discipline' => $validated['discipline'],
            'current_revision' => $currentRevision,
            'file_path' => $filePath,
            'is_approved_for_construction' => $isIfc,
            'uploaded_by' => $user->id,
        ]);

        $document->load('uploader');

        return ApiResponse::success($document, 'تم حفظ المستند/المخطط بنجاح', 201);
    }
}

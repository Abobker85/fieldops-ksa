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
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'document_code' => ['required', 'string', 'max:100'],
            'discipline' => ['required', 'in:architectural,structural,mechanical,electrical,contracts,permits'],
            'current_revision' => ['nullable', 'string', 'max:10'],
            'is_approved_for_construction' => ['nullable', 'boolean'],
            'file' => ['required', 'file', 'mimes:pdf,dwg,dxf,png,jpg,jpeg,zip', 'max:51200'], // max 50MB
        ]);

        $filePath = $request->file('file')->store("projects/{$project->id}/documents", 'public');

        $document = ProjectDocument::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'document_code' => $validated['document_code'],
            'discipline' => $validated['discipline'],
            'current_revision' => $validated['current_revision'] ?? 'Rev 00',
            'file_path' => $filePath,
            'is_approved_for_construction' => $request->boolean('is_approved_for_construction'),
            'uploaded_by' => $request->user()->id,
        ]);

        $document->load('uploader');

        return ApiResponse::success($document, 'تم حفظ المستند/المخطط بنجاح', 201);
    }
}

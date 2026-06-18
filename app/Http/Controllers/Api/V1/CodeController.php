<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CodeFileResource;
use App\Models\CodeFile;
use App\Models\CodeProject;
use App\Services\CodeTreeService;
use Illuminate\Http\JsonResponse;

final class CodeController extends Controller
{
    public function __construct(
        private readonly CodeTreeService $codeTreeService,
    ) {}

    public function tree(): JsonResponse
    {
        return response()->json($this->codeTreeService->getFullTree());
    }

    public function projects(): JsonResponse
    {
        $projects = CodeProject::orderBy('name')->get();

        return response()->json($projects->map(fn (CodeProject $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'description' => $p->description,
        ]));
    }

    public function projectTree(string $slug): JsonResponse
    {
        $project = CodeProject::where('slug', $slug)->firstOrFail();

        return response()->json($this->codeTreeService->getProjectTree($project));
    }

    public function show(string $path): CodeFileResource
    {
        $file = CodeFile::with('linkedArticle')
            ->where('path', $path)
            ->firstOrFail();

        return new CodeFileResource($file);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProjectController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $projects = Project::orderBy('featured', 'desc')->orderBy('created_at', 'desc')->get();

        return ProjectResource::collection($projects);
    }

    public function show(string $slug): ProjectResource
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        return new ProjectResource($project);
    }
}

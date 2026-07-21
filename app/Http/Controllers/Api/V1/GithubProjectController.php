<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\GithubProjectResource;
use App\Services\GithubProjectService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Contrôleur API pour exposer la liste des projets GitHub publics.
 */
final class GithubProjectController extends Controller
{
    public function __construct(
        private readonly GithubProjectService $githubProjectService,
    ) {}

    /**
     * Retourne la liste des projets GitHub publiés, triés par sort_order.
     */
    public function index(): AnonymousResourceCollection
    {
        $projects = $this->githubProjectService->listPublished();

        return GithubProjectResource::collection($projects);
    }
}

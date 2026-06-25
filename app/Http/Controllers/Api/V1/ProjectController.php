<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Contrôleur API pour gérer les projets de réalisation.
 * Justification : Expose les réalisations publiques de Loïc Bonin pour le portfolio front-end,
 * en déléguant la récupération des données au service associé.
 */
final class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {
        //
    }

    /**
     * Récupère la liste de tous les projets ordonnés par préférence puis par date.
     * Choix : Utilise ProjectResource pour formater uniformément les sorties JSON.
     */
    public function index(): AnonymousResourceCollection
    {
        $projects = $this->projectService->listAll();

        return ProjectResource::collection($projects);
    }

    /**
     * Récupère les détails d'un projet individuel identifié par son slug unique.
     * Choix : Utilise ProjectService et lève une exception 404 si introuvable.
     */
    public function show(string $slug): ProjectResource
    {
        $project = $this->projectService->findBySlug($slug);

        if ($project === null) {
            abort(404);
        }

        return new ProjectResource($project);
    }
}

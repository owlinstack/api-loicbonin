<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Contrôleur API pour gérer les projets de réalisation.
 * Justification : Expose les réalisations publiques de Loïc Bonin pour le portfolio front-end.
 */
final class ProjectController extends Controller
{
    /**
     * Récupère la liste de tous les projets ordonnés par préférence puis par date.
     * Choix : Utilise ProjectResource pour formater uniformément les sorties JSON.
     */
    public function index(): AnonymousResourceCollection
    {
        $projects = Project::query()->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Récupère les détails d'un projet individuel identifié par son slug unique.
     * Choix : Utilise firstOrFail() pour lever une exception de modèle introuvable (404) nativement gérée par Laravel.
     */
    public function show(string $slug): ProjectResource
    {
        $project = Project::query()->where('slug', $slug)->firstOrFail();

        return new ProjectResource($project);
    }
}

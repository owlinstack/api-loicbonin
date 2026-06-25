<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CodeFileResource;
use App\Services\CodeTreeService;
use Illuminate\Http\JsonResponse;

/**
 * Contrôleur API pour la navigation dans l'arborescence des projets et fichiers de code.
 * Justification : Expose les structures de dossiers/fichiers interactives (tree) pour le front-end,
 * ainsi que le contenu individuel des fichiers sources, en déléguant tout l'accès aux données à CodeTreeService.
 */
final class CodeController extends Controller
{
    public function __construct(
        private readonly CodeTreeService $codeTreeService,
    ) {}

    /**
     * Retourne l'arborescence complète (projets, dossiers et fichiers).
     * Choix : Délègue au service CodeTreeService pour optimiser le rendu et minimiser le nombre de requêtes SQL.
     */
    public function tree(): JsonResponse
    {
        return response()->json($this->codeTreeService->getFullTree());
    }

    /**
     * Retourne la liste des projets de code publiés (version allégée sans l'arborescence).
     * Choix : Utilise CodeTreeService pour lister les projets publiés et formater la sortie.
     */
    public function projects(): JsonResponse
    {
        $projects = $this->codeTreeService->listPublishedProjects();

        return response()->json($projects->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'description' => $p->description,
        ]));
    }

    /**
     * Retourne l'arborescence des répertoires spécifique à un projet identifié par son slug.
     * Choix : Utilise CodeTreeService et lève une exception 404 si le projet est introuvable ou non publié.
     */
    public function projectTree(string $slug): JsonResponse
    {
        $project = $this->codeTreeService->getProjectBySlug($slug);

        if ($project === null) {
            abort(404);
        }

        return response()->json($this->codeTreeService->getProjectTree($project));
    }

    /**
     * Retourne les détails et le contenu d'un fichier de code source spécifique.
     * Choix : Utilise CodeTreeService et lève 404 si le fichier n'existe pas.
     */
    public function show(string $path): CodeFileResource
    {
        $file = $this->codeTreeService->getFileByPath($path);

        if ($file === null) {
            abort(404);
        }

        return new CodeFileResource($file);
    }
}

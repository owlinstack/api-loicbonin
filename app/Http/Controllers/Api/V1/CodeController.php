<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CodeFileResource;
use App\Models\CodeFile;
use App\Models\CodeProject;
use App\Services\CodeTreeService;
use Illuminate\Http\JsonResponse;

/**
 * Contrôleur API pour la navigation dans l'arborescence des projets et fichiers de code.
 * Justification : Expose les structures de dossiers/fichiers interactives (tree) pour le front-end,
 * ainsi que le contenu individuel des fichiers sources.
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
     * Choix : Le mappage manuel évite de charger récursivement les dossiers racine pour chaque projet, optimisant les performances.
     */
    public function projects(): JsonResponse
    {
        $projects = CodeProject::query()->where('is_published', true)->orderBy('name', 'asc')->get();

        return response()->json($projects->map(fn (CodeProject $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'description' => $p->description,
        ]));
    }

    /**
     * Retourne l'arborescence des répertoires spécifique à un projet identifié par son slug.
     * Choix : Utilise CodeTreeService après avoir sécurisé l'accès au projet publié via firstOrFail().
     */
    public function projectTree(string $slug): JsonResponse
    {
        $project = CodeProject::query()->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return response()->json($this->codeTreeService->getProjectTree($project));
    }

    /**
     * Retourne les détails et le contenu d'un fichier de code source spécifique.
     * Choix : Eager loading de linkedArticle pour lier facilement le code à son explication textuelle (blog).
     */
    public function show(string $path): CodeFileResource
    {
        $file = CodeFile::query()
            ->with('linkedArticle')
            ->where('path', $path)
            ->firstOrFail();

        return new CodeFileResource($file);
    }
}

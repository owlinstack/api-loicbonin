<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ListArticlesRequest;
use App\Http\Resources\V1\ArticleCollection;
use App\Http\Resources\V1\ArticleResource;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur API pour gérer les articles de blog.
 * Justification : Expose les endpoints publics pour lister et afficher les articles,
 * en assurant la transition entre la validation HTTP (FormRequest) et la couche Service.
 */
final class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {
        //
    }

    /**
     * Liste les articles publiés avec pagination et filtres optionnels.
     * Choix : Utilise ListArticlesRequest pour la validation et délègue la logique de filtre au Service.
     */
    public function index(ListArticlesRequest $request): ArticleCollection
    {
        $validated = $request->validated();

        $category = $validated['category'] ?? null;
        $tag = $validated['tag'] ?? null;
        $page = $validated['page'] ?? 1;
        $pageSize = $validated['pageSize'] ?? 10;

        $paginated = $this->articleService->listPublished(
            category: $category,
            tag: $tag,
            page: $page,
            pageSize: $pageSize,
        );

        return new ArticleCollection($paginated);
    }

    /**
     * Affiche un article individuel recherché par son slug.
     * Choix : Utilise ArticleService et retourne une ressource formatée ou une erreur 404 standardisée.
     */
    public function show(string $slug): ArticleResource|JsonResponse
    {
        $article = $this->articleService->findBySlug($slug);

        if ($article === null) {
            return response()->json(
                ['message' => 'Article not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return new ArticleResource($article);
    }
}

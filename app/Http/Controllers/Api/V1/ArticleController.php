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
     */
    public function index(ListArticlesRequest $request): ArticleCollection
    {
        $validated = $request->validated();

        // Extraction et typage explicite (type casting) pour satisfaire PHPStan
        // et sécuriser les types de paramètres reçus sous forme de chaînes de l'URL

        $category = isset($validated['category']) ? (string) $validated['category'] : null;
        $tag = isset($validated['tag']) ? (string) $validated['tag'] : null;
        $page = isset($validated['page']) ? (int) $validated['page'] : 1;
        $pageSize = isset($validated['pageSize']) ? (int) $validated['pageSize'] : 10;

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

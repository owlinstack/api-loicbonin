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

final class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {
        //
    }

    public function index(ListArticlesRequest $request): ArticleCollection
    {
        $validated = $request->validated();

        $paginated = $this->articleService->listPublished(
            category: $validated['category'] ?? null,
            tag: $validated['tag'] ?? null,
            page: isset($validated['page']) ? (int) $validated['page'] : 1,
            pageSize: isset($validated['pageSize']) ? (int) $validated['pageSize'] : 10,
        );

        return new ArticleCollection($paginated);
    }

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

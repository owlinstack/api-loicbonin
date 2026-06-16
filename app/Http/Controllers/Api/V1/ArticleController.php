<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArticleCollection;
use App\Http\Resources\V1\ArticleResource;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {}

    public function index(Request $request): ArticleCollection
    {
        $paginated = $this->articleService->listPublished(
            category: $request->query('category'),
            tag:      $request->query('tag'),
            page:     (int) $request->query('page', '1'),
            pageSize: (int) $request->query('pageSize', '10'),
        );

        return new ArticleCollection($paginated);
    }

    public function show(string $slug): ArticleResource|JsonResponse
    {
        $article = $this->articleService->findBySlug($slug);

        if (! $article) {
            return response()->json(
                ['message' => 'Article not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return new ArticleResource($article);
    }
}

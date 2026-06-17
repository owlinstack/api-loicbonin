<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class ArticleCollection extends ResourceCollection
{
    public static $wrap = null;

    public $collects = ArticleResource::class;

    /**
     * Correspond à l'interface TypeScript `PaginatedArticles`.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\Article> $paginator */
        $paginator = $this->resource;

        return [
            'articles' => $this->collection,
            'total'    => $paginator->total(),
            'page'     => $paginator->currentPage(),
            'pageSize' => $paginator->perPage(),
        ];
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->toArray($request));
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ArticleService
{
    /**
     * Retourne les articles publiés, filtrés et paginés.
     */
    public function listPublished(
        ?string $category = null,
        ?string $tag = null,
        int $page = 1,
        int $pageSize = 10,
    ): LengthAwarePaginator {
        return Article::query()
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->when($category, fn ($q, $cat) => $q->whereRelation('category', 'slug', $cat))
            ->when($tag, fn ($q, $t) => $q->whereRelation('tags', 'name', $t))
            ->with(['category', 'tags'])
            ->orderByDesc('published_at')
            ->paginate(perPage: $pageSize, page: $page);
    }

    public function findBySlug(string $slug): ?Article
    {
        return Article::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->with(['category', 'tags'])
            ->first();
    }
}

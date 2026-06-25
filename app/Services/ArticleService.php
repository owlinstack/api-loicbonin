<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service métier gérant les opérations sur les articles de blog.
 * Justification : Centralise la logique d'accès aux articles publiés (avec filtres, pagination, tri),
 * et regroupe l'eager loading des relations complexes pour éliminer tout risque de requêtes N+1.
 */
final class ArticleService
{
    /**
     * Retourne les articles publiés, filtrés et paginés.
     *
     * @return LengthAwarePaginator<int, Article>
     */
    public function listPublished(
        ?string $category = null,
        ?string $tag = null,
        int $page = 1,
        int $pageSize = 10,
    ): LengthAwarePaginator {
        return Article::query()
            ->where('status', ArticleStatus::Published)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->when($category, fn ($q, $cat) => $q->whereRelation('category', 'slug', $cat))
            ->when($tag, fn ($q, $t) => $q->whereRelation('tags', 'name', $t))
            ->with([
                'category',
                'tags',
                'codeFile.folder.parent.parent.codeProject',
                'codeFolder.parent.parent.codeProject',
                'codeProject.rootFolders',
            ])
            ->orderByDesc('published_at')
            ->paginate(perPage: $pageSize, page: $page);
    }

    public function findBySlug(string $slug): ?Article
    {
        return Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('slug', $slug)
            ->with([
                'category',
                'tags',
                'codeFile.folder.parent.parent.codeProject',
                'codeFolder.parent.parent.codeProject',
                'codeProject.rootFolders',
            ])
            ->first();
    }
}

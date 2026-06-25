<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service gérant la logique métier pour les catégories d'articles.
 * Justification : Isole les requêtes d'accès et d'agrégation des catégories de la couche de transport HTTP.
 */
final class CategoryService
{
    /**
     * Récupère la liste de toutes les catégories avec le nombre d'articles publiés associés.
     *
     * @return Collection<int, Category>
     */
    public function listWithPublishedArticlesCount(): Collection
    {
        return Category::withCount(['articles' => function (Builder $query): void {
            $query->where('status', ArticleStatus::Published);
        }])->get();
    }
}

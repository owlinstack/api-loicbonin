<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Contrôleur API pour gérer les catégories d'articles.
 * Justification : Expose les catégories disponibles pour structurer le menu de navigation,
 * et compte le nombre d'articles associés de manière optimisée.
 */
final class CategoryController extends Controller
{
    /**
     * Récupère la liste de toutes les catégories avec le nombre d'articles publiés.
     * Choix : Utilise Eloquent withCount() pour éviter les requêtes N+1 et obtenir le compte en une seule requête SQL.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::withCount(['articles' => function (Builder $query): void {
            $query->where('status', ArticleStatus::Published);
        }])->get();

        return CategoryResource::collection($categories);
    }
}

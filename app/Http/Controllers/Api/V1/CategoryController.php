<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Contrôleur API pour gérer les catégories d'articles.
 * Justification : Expose les catégories disponibles pour structurer le menu de navigation,
 * en déléguant la récupération et le comptage optimisé des articles au service associé.
 */
final class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {
        //
    }

    /**
     * Récupère la liste de toutes les catégories avec le nombre d'articles publiés.
     * Choix : Utilise CategoryResource pour formater uniformément les sorties JSON.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = $this->categoryService->listWithPublishedArticlesCount();

        return CategoryResource::collection($categories);
    }
}

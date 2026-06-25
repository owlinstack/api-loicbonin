<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

/**
 * Contrôleur API pour gérer les tags (mots-clés) associés aux articles de blog.
 * Justification : Expose la liste brute des mots-clés disponibles pour les filtres du portfolio.
 */
final class TagController extends Controller
{
    /**
     * Récupère la liste globale de tous les noms de tags.
     * Choix : Utilise pluck() pour extraire uniquement la colonne 'name' sous forme de liste plate au lieu de charger des modèles complets.
     */
    public function index(): JsonResponse
    {
        $tags = Tag::query()->pluck('name');

        return response()->json($tags);
    }
}

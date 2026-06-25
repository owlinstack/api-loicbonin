<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;

/**
 * Contrôleur API pour gérer les tags (mots-clés) associés aux articles de blog.
 * Justification : Expose la liste brute des mots-clés disponibles pour les filtres du portfolio,
 * en déléguant la logique d'accès au service associé.
 */
final class TagController extends Controller
{
    public function __construct(
        private readonly TagService $tagService,
    ) {
        //
    }

    /**
     * Récupère la liste globale de tous les noms de tags.
     * Choix : Utilise TagService et retourne un JSON plat.
     */
    public function index(): JsonResponse
    {
        $tags = $this->tagService->listAllNames();

        return response()->json($tags);
    }
}

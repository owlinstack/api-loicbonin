<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource de présentation pour une catégorie.
 * Justification : Formate les catégories d'articles,
 * et utilise une détection optimisée du count (soit déjà calculé avec withCount, soit via requête fallback).
 *
 * @property Category $resource
 */
final class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->resource->slug,
            'label' => $this->resource->label,
            'count' => $this->resource->articles_count ?? $this->resource->articles()->count(),
        ];
    }
}

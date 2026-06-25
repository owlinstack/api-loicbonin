<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\CodeProject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource de présentation pour un projet de code source complet.
 * Justification : Représente le point d'entrée de l'arborescence de fichiers du projet,
 * et renvoie sa structure hiérarchique complète (dossiers racines et sous-dossiers).
 *
 * @property CodeProject $resource
 */
final class CodeProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rootFolders = $this->resource->relationLoaded('rootFolders')
            ? $this->resource->rootFolders
            : $this->resource->rootFolders()->get();

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'tree' => CodeFolderResource::collection($rootFolders)->toArray($request),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\GithubProject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource API de présentation pour un projet GitHub.
 * Convertit le format de clé snake_case vers camelCase pour le contrat d'API Next.js.
 *
 * @property GithubProject $resource
 */
final class GithubProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'githubUrl' => $this->resource->github_url,
            'status' => $this->resource->status->value,
            'sortOrder' => $this->resource->sort_order,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Project $resource
 */
final class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'slug' => $this->resource->slug,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'longDescription' => $this->resource->long_description,
            'techStack' => $this->resource->tech_stack,
            'liveUrl' => $this->resource->live_url,
            'repoUrl' => $this->resource->repo_url,
            'featured' => $this->resource->featured,
            'year' => $this->resource->year,
        ];
    }
}

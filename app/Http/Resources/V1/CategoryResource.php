<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Category $resource
 */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->resource->slug,
            'label' => $this->resource->label,
            'count' => $this->resource->articles_count ?? $this->resource->articles()->count(),
        ];
    }
}

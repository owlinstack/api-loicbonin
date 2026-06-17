<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\CodeProject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CodeProject $resource
 */
final class CodeProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rootFolders = $this->resource->folders()
            ->whereNull('parent_id')
            ->get();

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'tree' => CodeFolderResource::collection($rootFolders)->toArray($request),
        ];
    }
}

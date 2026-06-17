<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\CodeFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CodeFile $resource
 */
final class CodeFileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'path' => $this->resource->path,
            'language' => $this->resource->language,
            'content' => $this->resource->content,
            'linkedArticleSlug' => $this->resource->linkedArticle?->slug,
            'linkedArticleTitle' => $this->resource->linkedArticle?->title,
        ];
    }
}

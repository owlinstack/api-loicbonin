<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\CodeFolder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CodeFolder $resource
 */
final class CodeFolderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $projectSlug = null;
        $folder = $this->resource;
        while ($folder) {
            if ($folder->code_project_id) {
                $projectSlug = $folder->codeProject?->slug;
                break;
            }
            $folder = $folder->parent;
        }

        $subfolders = $this->resource->children->map(function (CodeFolder $folder) use ($request) {
            return (new self($folder))->toArray($request);
        })->all();

        $files = $this->resource->files->map(function ($file) use ($projectSlug) {
            return [
                'name' => $file->name,
                'path' => $file->path,
                'language' => $file->language,
                'content' => $file->content,
                'linkedArticleSlug' => $file->linkedArticle?->slug,
                'linkedArticleTitle' => $file->linkedArticle?->title,
                'projectSlug' => $projectSlug,
            ];
        })->all();

        return [
            'name' => $this->resource->name,
            'path' => $this->resource->path,
            'children' => array_merge($subfolders, $files),
            'projectSlug' => $projectSlug,
        ];
    }
}

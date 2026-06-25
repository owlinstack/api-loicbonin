<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\CodeFolder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource de présentation pour un dossier de code source.
 * Justification : Construit l'arborescence des dossiers et fichiers de manière récursive.
 * Note de performance : Grâce à l'eager loading des relations (children, files) en amont dans le Service,
 * cette récursion est entièrement résolue en mémoire sans provoquer de requêtes SQL N+1.
 *
 * @property CodeFolder $resource
 */
final class CodeFolderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $projectSlug = $this->resource->getProjectSlug();

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

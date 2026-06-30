<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource de présentation pour un article individuel.
 * Justification : Transforme le modèle Article en un format JSON normalisé pour le frontend Next.js,
 * gère le formatage des dates, extrait les noms des tags et lie récursivement les fichiers/dossiers/projets de code associés.
 *
 * @mixin Article
 */
final class ArticleResource extends JsonResource
{
    /**
     * Correspond à l'interface TypeScript `Article`.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'category' => $this->categories->first()?->slug,
            'categories' => $this->categories->pluck('slug')->all(),
            'tags' => $this->tags->pluck('name')->all(),
            'publishedAt' => ($this->published_at ?? $this->created_at)?->toDateString(),
            'readingTime' => $this->reading_time,
            'featured' => $this->featured,
            'codeFile' => $this->codeFile ? new CodeFileResource($this->codeFile) : null,
            'codeFolder' => $this->codeFolder ? new CodeFolderResource($this->codeFolder) : null,
            'codeProject' => ($this->codeProject && $this->codeProject->is_published) ? new CodeProjectResource($this->codeProject) : null,
        ];
    }
}

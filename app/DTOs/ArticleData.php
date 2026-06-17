<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ArticleCategory;

/**
 * EXEMPLE (Non utilisé dans le projet)
 *
 * Ce DTO sert uniquement d'exemple de structure de transfert de données.
 * Il n'est pas actif dans le projet car les transformations de modèles et la gestion
 * des relations dynamiques sont confiées directement aux API Resources de Laravel (ArticleResource).
 */
final readonly class ArticleData
{
    /**
     * @param  list<string>  $tags
     */
    public function __construct(
        public string $id,
        public string $slug,
        public string $title,
        public string $excerpt,
        public string $content,
        public ArticleCategory $category,
        public array $tags,
        public string $publishedAt,
        public int $readingTime,
        public bool $featured = false,
    ) {
        //
    }
}

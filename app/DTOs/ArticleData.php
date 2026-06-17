<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ArticleCategory;

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
    ) {}
}

<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\GithubProjectStatus;

/**
 * Data Transfer Object pour encadrer la structure des projets GitHub.
 */
final readonly class GithubProjectData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public string $githubUrl,
        public GithubProjectStatus $status,
        public int $sortOrder = 0,
    ) {}
}

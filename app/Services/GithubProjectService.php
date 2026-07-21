<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\GithubProjectStatus;
use App\Models\GithubProject;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service gérant la logique métier pour les projets GitHub.
 */
final class GithubProjectService
{
    /**
     * Récupère tous les projets GitHub publiés, triés par sort_order.
     *
     * @return Collection<int, GithubProject>
     */
    public function listPublished(): Collection
    {
        return GithubProject::query()
            ->where('status', GithubProjectStatus::Published)
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

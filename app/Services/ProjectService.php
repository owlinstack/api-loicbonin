<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service gérant la logique métier pour les projets de réalisations.
 * Justification : Isole les requêtes d'accès aux projets de la couche de transport HTTP (contrôleur).
 */
final class ProjectService
{
    /**
     * Récupère tous les projets ordonnés par préférence de tri puis par date de création.
     *
     * @return Collection<int, Project>
     */
    public function listAll(): Collection
    {
        return Project::query()
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Récupère un projet individuel par son slug.
     * Renvoie null s'il n'est pas trouvé.
     */
    public function findBySlug(string $slug): ?Project
    {
        /** @var Project|null $project */
        $project = Project::query()
            ->where('slug', $slug)
            ->first();

        return $project;
    }
}

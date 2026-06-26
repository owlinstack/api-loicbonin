<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Collection;

/**
 * Service gérant la logique métier pour les tags d'articles.
 * Justification : Isole les requêtes d'accès aux tags de la couche de transport HTTP.
 */
final class TagService
{
    /**
     * Récupère la liste globale de tous les noms de tags.
     *
     * @return Collection<int, string>
     */
    public function listAllNames(): Collection
    {
        return Tag::query()
            ->pluck('name')
            ->values()
            ->map(fn (mixed $name): string => \is_string($name) ? $name : '');
    }
}

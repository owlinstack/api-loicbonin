<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum natif représentant les statuts d'un projet GitHub.
 * Justification : Sécurise les états de publication en base de données et dans l'admin Filament.
 */
enum GithubProjectStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}

<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum natif représentant les statuts possibles d'un article.
 * Justification : Sécurise les états de publication en base de données et évite
 * l'utilisation de chaînes "magiques" non contrôlées dans le code.
 */
enum ArticleStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}

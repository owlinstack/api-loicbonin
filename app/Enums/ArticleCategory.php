<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * EXEMPLE (Non utilisé dans le projet)
 *
 * Cet Enum sert uniquement d'exemple de structure d'Enum statique.
 * Il n'est pas actif dans le projet car les catégories d'articles sont dynamiques
 * et gérées directement en base de données via la table `categories` et le modèle `Category`.
 */
enum ArticleCategory: string
{
    case React = 'react';
    case TypeScript = 'typescript';
    case Css = 'css';
    case Backend = 'backend';
    case Tooling = 'tooling';
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum ArticleCategory: string
{
    case React     = 'react';
    case TypeScript = 'typescript';
    case Css       = 'css';
    case Backend   = 'backend';
    case Tooling   = 'tooling';
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modèle représentant un projet de réalisation professionnelle.
 * Justification : Stocke les détails d'un projet du portfolio (description, technologies, URLs de démo/dépôt, ordre d'affichage).
 *
 * @property string $id
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string|null $long_description
 * @property array<int, string> $tech_stack
 * @property string|null $live_url
 * @property string|null $repo_url
 * @property bool $featured
 * @property int $sort_order
 * @property int|null $year
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Project extends Model
{
    use HasUlids;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'long_description',
        'tech_stack',
        'live_url',
        'repo_url',
        'featured',
        'sort_order',
        'year',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'featured' => 'boolean',
        'sort_order' => 'integer',
    ];
}

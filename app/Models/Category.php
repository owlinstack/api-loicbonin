<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Modèle représentant une catégorie d'articles de blog.
 * Justification : Regroupe les articles par thématiques principales (ex: Backend, Frontend)
 * pour structurer la navigation et faciliter le filtrage par sujet.
 *
 * @property string $id
 * @property string $slug
 * @property string $label
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Article> $articles
 */
final class Category extends Model
{
    use HasUlids;

    protected $fillable = [
        'slug',
        'label',
    ];

    /**
     * @return BelongsToMany<Article, $this>
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_category');
    }
}

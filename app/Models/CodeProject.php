<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Modèle représentant un projet de code source pour le portfolio.
 * Justification : Regroupe tous les dossiers et fichiers de code d'un projet spécifique
 * pour permettre leur affichage interactif au travers d'un explorateur de fichiers côté front-end.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $is_published
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, CodeFolder> $folders
 * @property-read Collection<int, CodeFolder> $rootFolders
 * @property-read Article|null $linkedArticle
 */
final class CodeProject extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    /**
     * @return HasMany<CodeFolder, $this>
     */
    public function folders(): HasMany
    {
        return $this->hasMany(CodeFolder::class, 'code_project_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<CodeFolder, $this>
     */
    public function rootFolders(): HasMany
    {
        return $this->hasMany(CodeFolder::class, 'code_project_id')
            ->whereNull('parent_id')
            ->orderBy('sort_order');
    }

    /**
     * @return HasOne<Article, $this>
     */
    public function linkedArticle(): HasOne
    {
        return $this->hasOne(Article::class, 'code_project_id');
    }
}

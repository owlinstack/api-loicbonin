<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Modèle représentant un dossier de code source dans l'arborescence d'un projet.
 * Justification : Modélise les relations récursives de l'arborescence (dossiers parents/enfants et fichiers enfants),
 * avec un système de cache interne pour la résolution du slug du projet associé.
 *
 * @property string $id
 * @property string $name
 * @property string $path
 * @property string|null $parent_id
 * @property string|null $code_project_id
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CodeFolder|null $parent
 * @property-read Collection<int, CodeFolder> $children
 * @property-read Collection<int, CodeFile> $files
 * @property-read CodeProject|null $codeProject
 * @property-read Article|null $linkedArticle
 */
final class CodeFolder extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'path',
        'parent_id',
        'code_project_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Cache du slug du projet pour le cycle de vie de la requête.
     *
     * @var array<string, ?string>
     */
    private static array $projectSlugCache = [];

    /**
     * Résout le slug du projet associé de manière récursive avec un cache mémoire.
     */
    public function getProjectSlug(): ?string
    {
        if (array_key_exists($this->id, self::$projectSlugCache)) {
            return self::$projectSlugCache[$this->id];
        }

        if ($this->code_project_id) {
            $slug = $this->codeProject?->slug;
            self::$projectSlugCache[$this->id] = $slug;

            return $slug;
        }

        if ($this->parent_id) {
            $slug = $this->parent?->getProjectSlug();
            self::$projectSlugCache[$this->id] = $slug;

            return $slug;
        }

        self::$projectSlugCache[$this->id] = null;

        return null;
    }

    /**
     * @return BelongsTo<CodeFolder, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CodeFolder::class, 'parent_id');
    }

    /**
     * @return HasMany<CodeFolder, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(CodeFolder::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<CodeFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(CodeFile::class, 'folder_id')->orderBy('sort_order');
    }

    /**
     * @return BelongsTo<CodeProject, $this>
     */
    public function codeProject(): BelongsTo
    {
        return $this->belongsTo(CodeProject::class, 'code_project_id');
    }

    /**
     * @return HasOne<Article, $this>
     */
    public function linkedArticle(): HasOne
    {
        return $this->hasOne(Article::class, 'code_folder_id');
    }
}

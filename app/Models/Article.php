<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Modèle représentant un article de blog.
 * Justification : Gère le contenu éditorial du blog avec un statut de publication (brouillon/publié),
 * prend en charge l'association aux catégories/mots-clés, et se lie optionnellement à un fichier ou projet de code.
 *
 * @property string $id
 * @property string $slug
 * @property string $title
 * @property string $excerpt
 * @property string $content
 * @property ArticleStatus $status
 * @property int $reading_time
 * @property bool $featured
 * @property Carbon|null $published_at
 * @property string|null $code_file_id
 * @property string|null $code_folder_id
 * @property string|null $code_project_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, Tag> $tags
 * @property-read CodeFile|null $codeFile
 * @property-read CodeFolder|null $codeFolder
 * @property-read CodeProject|null $codeProject
 */
final class Article extends Model
{
    use HasUlids;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'status',
        'reading_time',
        'featured',
        'published_at',
        'code_file_id',
        'code_folder_id',
        'code_project_id',
    ];

    protected $casts = [
        'status' => ArticleStatus::class,
        'featured' => 'boolean',
        'published_at' => 'datetime',
        'reading_time' => 'integer',
    ];

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return BelongsTo<CodeFile, $this>
     */
    public function codeFile(): BelongsTo
    {
        return $this->belongsTo(CodeFile::class, 'code_file_id');
    }

    /**
     * @return BelongsTo<CodeFolder, $this>
     */
    public function codeFolder(): BelongsTo
    {
        return $this->belongsTo(CodeFolder::class, 'code_folder_id');
    }

    /**
     * @return BelongsTo<CodeProject, $this>
     */
    public function codeProject(): BelongsTo
    {
        return $this->belongsTo(CodeProject::class, 'code_project_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasUlids;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'category_id',
        'status',
        'reading_time',
        'featured',
        'published_at',
    ];

    protected $casts = [
        'status' => ArticleStatus::class,
        'featured' => 'boolean',
        'published_at' => 'datetime',
        'reading_time' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CodeFile, $this>
     */
    public function codeFiles(): HasMany
    {
        return $this->hasMany(CodeFile::class, 'linked_article_id');
    }
}

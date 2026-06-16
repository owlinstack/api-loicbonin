<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasUids;

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function codeFiles(): HasMany
    {
        return $this->hasMany(CodeFile::class, 'linked_article_id');
    }
}

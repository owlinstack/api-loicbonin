<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Article extends Model
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
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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

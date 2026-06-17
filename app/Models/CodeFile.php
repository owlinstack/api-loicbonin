<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class CodeFile extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'path',
        'language',
        'content',
        'folder_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * @return BelongsTo<CodeFolder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(CodeFolder::class, 'folder_id');
    }

    /**
     * @return HasOne<Article, $this>
     */
    public function linkedArticle(): HasOne
    {
        return $this->hasOne(Article::class, 'code_file_id');
    }
}

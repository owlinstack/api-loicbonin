<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeFile extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'path',
        'language',
        'content',
        'folder_id',
        'linked_article_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(CodeFolder::class, 'folder_id');
    }

    public function linkedArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'linked_article_id');
    }
}

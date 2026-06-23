<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

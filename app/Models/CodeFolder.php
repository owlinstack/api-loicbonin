<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CodeFolder extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'path',
        'parent_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

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
}

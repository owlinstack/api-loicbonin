<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUids;

    protected $fillable = [
        'slug',
        'label',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}

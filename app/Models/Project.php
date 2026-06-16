<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasUlids;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'long_description',
        'tech_stack',
        'live_url',
        'repo_url',
        'featured',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'featured' => 'boolean',
    ];
}

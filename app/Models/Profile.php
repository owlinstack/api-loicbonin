<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class Profile extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'bio',
        'skills',
        'timeline',
        'show_timeline',
        'education',
        'show_education',
        'cv_url',
        'avatar_url',
    ];

    protected $casts = [
        'skills' => 'array',
        'timeline' => 'array',
        'show_timeline' => 'boolean',
        'education' => 'array',
        'show_education' => 'boolean',
    ];
}

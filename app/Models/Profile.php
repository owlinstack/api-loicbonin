<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'bio',
        'skills',
        'timeline',
        'cv_url',
    ];

    protected $casts = [
        'skills' => 'array',
        'timeline' => 'array',
    ];
}

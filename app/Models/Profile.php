<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modèle pour le profil utilisateur / développeur.
 *
 * @property string $id
 * @property string $name
 * @property string $bio
 * @property array<int, array{term: string, description: string}>|null $skills
 * @property bool|null $show_timeline
 * @property array<int, array{date: string, title: string, description: string}>|null $timeline
 * @property bool|null $show_education
 * @property array<int, array{date: string, title: string, description: string}>|null $education
 * @property string|null $cv_url
 * @property string|null $avatar_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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

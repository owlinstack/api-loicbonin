<?php

declare(strict_types=1);

namespace App\Models;

use App\DTOs\GithubProjectData;
use App\Enums\GithubProjectStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modèle représentant un projet/dépôt GitHub externe pour le portfolio.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $github_url
 * @property GithubProjectStatus $status
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class GithubProject extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'github_url',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'status' => GithubProjectStatus::class,
        'sort_order' => 'integer',
    ];

    /**
     * Convertit le modèle en DTO GithubProjectData.
     */
    public function toData(): GithubProjectData
    {
        return new GithubProjectData(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            description: $this->description,
            githubUrl: $this->github_url,
            status: $this->status,
            sortOrder: $this->sort_order,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\DTOs\ProfileData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource de présentation pour les données de profil.
 * Justification : Met en forme les données du DTO ProfileData pour la page de profil front-end,
 * en convertissant les chemins de fichiers d'avatar et de CV en URLs absolues de stockage ou fallbacks statiques.
 *
 * @property ProfileData $resource
 */
final class ProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'bio' => $this->resource->bio,
            'skills' => $this->resource->skills,
            'showTimeline' => $this->resource->showTimeline,
            'timeline' => $this->resource->timeline,
            'showEducation' => $this->resource->showEducation,
            'education' => $this->resource->education,
            'cvUrl' => $this->resource->cvPath ? asset('storage/'.$this->resource->cvPath) : '/cv-loic-bonin.pdf',
            'avatarUrl' => $this->resource->avatarPath ? asset('storage/'.$this->resource->avatarPath) : null,
        ];
    }
}

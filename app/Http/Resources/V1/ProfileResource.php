<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\DTOs\ProfileData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
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

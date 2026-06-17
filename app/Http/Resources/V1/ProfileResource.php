<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource['name'] ?? '',
            'bio' => $this->resource['bio'] ?? '',
            'skills' => $this->resource['skills'] ?? [],
            'timeline' => $this->resource['timeline'] ?? [],
            'education' => $this->resource['education'] ?? [],
            'cvUrl' => $this->resource['cvUrl'] ?? null,
            'avatarUrl' => $this->resource['avatarUrl'] ?? null,
        ];
    }
}

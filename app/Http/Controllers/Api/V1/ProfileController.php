<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProfileResource;
use App\Services\ProfileService;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {
        //
    }

    public function show(): ProfileResource
    {
        return new ProfileResource($this->profileService->getProfileData());
    }
}

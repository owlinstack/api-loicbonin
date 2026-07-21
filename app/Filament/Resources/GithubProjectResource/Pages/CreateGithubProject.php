<?php

declare(strict_types=1);

namespace App\Filament\Resources\GithubProjectResource\Pages;

use App\Filament\Resources\GithubProjectResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateGithubProject extends CreateRecord
{
    protected static string $resource = GithubProjectResource::class;
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\GithubProjectResource\Pages;

use App\Filament\Resources\GithubProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListGithubProjects extends ListRecords
{
    protected static string $resource = GithubProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

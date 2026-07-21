<?php

declare(strict_types=1);

namespace App\Filament\Resources\GithubProjectResource\Pages;

use App\Filament\Resources\GithubProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditGithubProject extends EditRecord
{
    protected static string $resource = GithubProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

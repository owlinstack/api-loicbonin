<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeProjectResource\Pages;

use App\Filament\Resources\CodeProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListCodeProjects extends ListRecords
{
    protected static string $resource = CodeProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

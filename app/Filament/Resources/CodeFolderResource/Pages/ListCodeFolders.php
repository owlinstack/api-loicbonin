<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeFolderResource\Pages;

use App\Filament\Resources\CodeFolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListCodeFolders extends ListRecords
{
    protected static string $resource = CodeFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeFileResource\Pages;

use App\Filament\Resources\CodeFileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCodeFiles extends ListRecords
{
    protected static string $resource = CodeFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

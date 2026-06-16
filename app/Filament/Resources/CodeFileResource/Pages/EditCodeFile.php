<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeFileResource\Pages;

use App\Filament\Resources\CodeFileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCodeFile extends EditRecord
{
    protected static string $resource = CodeFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

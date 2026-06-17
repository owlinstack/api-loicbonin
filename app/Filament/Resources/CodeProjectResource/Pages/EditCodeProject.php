<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeProjectResource\Pages;

use App\Filament\Resources\CodeProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCodeProject extends EditRecord
{
    protected static string $resource = CodeProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

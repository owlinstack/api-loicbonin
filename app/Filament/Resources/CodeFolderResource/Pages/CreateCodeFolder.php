<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeFolderResource\Pages;

use App\Filament\Resources\CodeFolderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCodeFolder extends CreateRecord
{
    protected static string $resource = CodeFolderResource::class;
}

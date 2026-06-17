<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeFileResource\Pages;

use App\Filament\Resources\CodeFileResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCodeFile extends CreateRecord
{
    protected static string $resource = CodeFileResource::class;
}

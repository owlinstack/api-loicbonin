<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeProjectResource\Pages;

use App\Filament\Resources\CodeProjectResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCodeProject extends CreateRecord
{
    protected static string $resource = CodeProjectResource::class;
}

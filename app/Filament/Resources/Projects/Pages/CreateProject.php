<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Concerns\CreatesScopedToCurrentProfile;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    use CreatesScopedToCurrentProfile;

    protected static string $resource = ProjectResource::class;
}

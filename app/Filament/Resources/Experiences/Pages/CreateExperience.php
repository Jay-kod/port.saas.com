<?php

namespace App\Filament\Resources\Experiences\Pages;

use App\Filament\Resources\Concerns\CreatesScopedToCurrentProfile;
use App\Filament\Resources\Experiences\ExperienceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExperience extends CreateRecord
{
    use CreatesScopedToCurrentProfile;

    protected static string $resource = ExperienceResource::class;
}

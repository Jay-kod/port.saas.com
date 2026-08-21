<?php

namespace App\Filament\Resources\Skills\Pages;

use App\Filament\Resources\Concerns\CreatesScopedToCurrentProfile;
use App\Filament\Resources\Skills\SkillResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSkill extends CreateRecord
{
    use CreatesScopedToCurrentProfile;

    protected static string $resource = SkillResource::class;
}

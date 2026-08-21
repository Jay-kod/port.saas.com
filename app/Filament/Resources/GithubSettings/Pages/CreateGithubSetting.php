<?php

namespace App\Filament\Resources\GithubSettings\Pages;

use App\Filament\Resources\Concerns\CreatesScopedToCurrentProfile;
use App\Filament\Resources\GithubSettings\GithubSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGithubSetting extends CreateRecord
{
    use CreatesScopedToCurrentProfile;

    protected static string $resource = GithubSettingResource::class;
}

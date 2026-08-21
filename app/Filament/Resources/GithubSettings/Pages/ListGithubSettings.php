<?php

namespace App\Filament\Resources\GithubSettings\Pages;

use App\Filament\Resources\GithubSettings\GithubSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGithubSettings extends ListRecords
{
    protected static string $resource = GithubSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

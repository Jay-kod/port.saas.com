<?php

namespace App\Filament\Resources\GithubSettings\Pages;

use App\Filament\Resources\GithubSettings\GithubSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGithubSetting extends EditRecord
{
    protected static string $resource = GithubSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

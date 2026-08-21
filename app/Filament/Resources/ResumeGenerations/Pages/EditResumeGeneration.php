<?php

namespace App\Filament\Resources\ResumeGenerations\Pages;

use App\Filament\Resources\ResumeGenerations\ResumeGenerationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResumeGeneration extends EditRecord
{
    protected static string $resource = ResumeGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

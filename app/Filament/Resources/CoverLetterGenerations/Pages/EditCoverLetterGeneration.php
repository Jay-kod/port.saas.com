<?php

namespace App\Filament\Resources\CoverLetterGenerations\Pages;

use App\Filament\Resources\CoverLetterGenerations\CoverLetterGenerationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCoverLetterGeneration extends EditRecord
{
    protected static string $resource = CoverLetterGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CoverLetterGenerations\Pages;

use App\Filament\Resources\CoverLetterGenerations\CoverLetterGenerationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoverLetterGenerations extends ListRecords
{
    protected static string $resource = CoverLetterGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Generate New Cover Letter'),
        ];
    }
}

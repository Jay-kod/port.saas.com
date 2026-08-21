<?php

namespace App\Filament\Resources\ResumeGenerations\Pages;

use App\Filament\Resources\ResumeGenerations\ResumeGenerationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResumeGenerations extends ListRecords
{
    protected static string $resource = ResumeGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

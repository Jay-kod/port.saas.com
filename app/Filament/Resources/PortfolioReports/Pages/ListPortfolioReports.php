<?php

namespace App\Filament\Resources\PortfolioReports\Pages;

use App\Filament\Resources\PortfolioReports\PortfolioReportResource;
use Filament\Resources\Pages\ListRecords;

class ListPortfolioReports extends ListRecords
{
    protected static string $resource = PortfolioReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

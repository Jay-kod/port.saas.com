<?php

namespace App\Filament\Resources\PortfolioReports;

use App\Filament\Resources\PortfolioReports\Pages\ListPortfolioReports;
use App\Filament\Resources\PortfolioReports\Tables\PortfolioReportsTable;
use App\Models\PortfolioReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PortfolioReportResource extends Resource
{
    protected static ?string $model = PortfolioReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Abuse Reports';

    protected static ?int $navigationSort = 95;

    public static function isScopedToTenant(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return PortfolioReportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPortfolioReports::route('/'),
        ];
    }
}

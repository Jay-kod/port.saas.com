<?php

namespace App\Filament\Resources\AiSettings;

use App\Filament\Resources\AiSettings\Pages\CreateAiSetting;
use App\Filament\Resources\AiSettings\Pages\EditAiSetting;
use App\Filament\Resources\AiSettings\Pages\ListAiSettings;
use App\Filament\Resources\AiSettings\Schemas\AiSettingForm;
use App\Filament\Resources\AiSettings\Tables\AiSettingsTable;
use App\Models\AiSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AiSettingResource extends Resource
{
    protected static ?string $model = AiSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AiSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiSettings::route('/'),
            'create' => CreateAiSetting::route('/create'),
            'edit' => EditAiSetting::route('/{record}/edit'),
        ];
    }
}

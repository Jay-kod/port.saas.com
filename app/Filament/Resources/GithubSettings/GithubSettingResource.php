<?php

namespace App\Filament\Resources\GithubSettings;

use App\Filament\Resources\Concerns\ScopedToCurrentProfile;
use App\Filament\Resources\GithubSettings\Pages\CreateGithubSetting;
use App\Filament\Resources\GithubSettings\Pages\EditGithubSetting;
use App\Filament\Resources\GithubSettings\Pages\ListGithubSettings;
use App\Filament\Resources\GithubSettings\Schemas\GithubSettingForm;
use App\Filament\Resources\GithubSettings\Tables\GithubSettingsTable;
use App\Models\GithubSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GithubSettingResource extends Resource
{
    use ScopedToCurrentProfile;

    protected static ?string $model = GithubSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return GithubSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GithubSettingsTable::configure($table);
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
            'index' => ListGithubSettings::route('/'),
            'create' => CreateGithubSetting::route('/create'),
            'edit' => EditGithubSetting::route('/{record}/edit'),
        ];
    }
}

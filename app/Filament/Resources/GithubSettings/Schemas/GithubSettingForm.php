<?php

namespace App\Filament\Resources\GithubSettings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GithubSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username'),
                Toggle::make('auto_sync')
                    ->required(),
                DateTimePicker::make('last_synced_at'),
            ]);
    }
}

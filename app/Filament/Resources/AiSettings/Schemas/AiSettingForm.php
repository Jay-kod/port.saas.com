<?php

namespace App\Filament\Resources\AiSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AiSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('provider')
                    ->required()
                    ->default('openai'),
                Textarea::make('api_key')
                    ->columnSpanFull(),
                TextInput::make('model'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

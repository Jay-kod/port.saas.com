<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('full_name')
                    ->required(),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->helperText('The public URL for this portfolio once Phase 3 routing lands: yoursaas.com/{slug}.'),
                TextInput::make('headline'),
                Textarea::make('bio')
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('location'),
                TextInput::make('avatar_path'),
                TextInput::make('resume_path'),
                Textarea::make('social_links')
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->default(true)
                    ->required(),
            ]);
    }
}

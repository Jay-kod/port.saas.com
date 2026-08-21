<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('summary'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('tech_stack')
                    ->columnSpanFull(),
                TextInput::make('repo_url')
                    ->url(),
                TextInput::make('live_url')
                    ->url(),
                FileUpload::make('image_path')
                    ->image(),
                Toggle::make('is_featured')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}

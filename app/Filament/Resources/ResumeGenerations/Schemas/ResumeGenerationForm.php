<?php

namespace App\Filament\Resources\ResumeGenerations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ResumeGenerationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('template_id')
                    ->numeric(),
                TextInput::make('job_title'),
                TextInput::make('company_name'),
                Textarea::make('job_description')
                    ->columnSpanFull(),
                Textarea::make('tailored_content')
                    ->columnSpanFull(),
                TextInput::make('pdf_path'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                Textarea::make('error_message')
                    ->columnSpanFull(),
            ]);
    }
}

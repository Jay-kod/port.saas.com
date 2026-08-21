<?php

namespace App\Filament\Resources\CoverLetterGenerations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CoverLetterGenerationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('job_title')
                    ->required()
                    ->placeholder('e.g. Senior Full-Stack Engineer'),
                TextInput::make('company_name')
                    ->required()
                    ->placeholder('e.g. Stripe, Acme Corp'),
                Textarea::make('job_description')
                    ->required()
                    ->rows(5)
                    ->placeholder('Paste job description details and requirements...')
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->label('Cover Letter Content')
                    ->rows(10)
                    ->placeholder('AI generated cover letter will appear here, or you can edit manually.')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->default('completed')
                    ->required(),
            ]);
    }
}

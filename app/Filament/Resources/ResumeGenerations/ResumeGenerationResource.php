<?php

namespace App\Filament\Resources\ResumeGenerations;

use App\Filament\Resources\Concerns\ScopedToCurrentProfile;
use App\Filament\Resources\ResumeGenerations\Pages\CreateResumeGeneration;
use App\Filament\Resources\ResumeGenerations\Pages\EditResumeGeneration;
use App\Filament\Resources\ResumeGenerations\Pages\ListResumeGenerations;
use App\Filament\Resources\ResumeGenerations\Schemas\ResumeGenerationForm;
use App\Filament\Resources\ResumeGenerations\Tables\ResumeGenerationsTable;
use App\Models\ResumeGeneration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResumeGenerationResource extends Resource
{
    use ScopedToCurrentProfile;

    protected static ?string $model = ResumeGeneration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ResumeGenerationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResumeGenerationsTable::configure($table);
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
            'index' => ListResumeGenerations::route('/'),
            'create' => CreateResumeGeneration::route('/create'),
            'edit' => EditResumeGeneration::route('/{record}/edit'),
        ];
    }
}

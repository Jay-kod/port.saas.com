<?php

namespace App\Filament\Resources\CoverLetterGenerations;

use App\Filament\Resources\Concerns\ScopedToCurrentProfile;
use App\Filament\Resources\CoverLetterGenerations\Pages\CreateCoverLetterGeneration;
use App\Filament\Resources\CoverLetterGenerations\Pages\EditCoverLetterGeneration;
use App\Filament\Resources\CoverLetterGenerations\Pages\ListCoverLetterGenerations;
use App\Filament\Resources\CoverLetterGenerations\Schemas\CoverLetterGenerationForm;
use App\Filament\Resources\CoverLetterGenerations\Tables\CoverLetterGenerationsTable;
use App\Models\CoverLetterGeneration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CoverLetterGenerationResource extends Resource
{
    use ScopedToCurrentProfile;

    protected static ?string $model = CoverLetterGeneration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Cover Letters';

    protected static ?string $modelLabel = 'Cover Letter';

    protected static ?int $navigationSort = 35;

    public static function form(Schema $schema): Schema
    {
        return CoverLetterGenerationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoverLetterGenerationsTable::configure($table);
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
            'index' => ListCoverLetterGenerations::route('/'),
            'create' => CreateCoverLetterGeneration::route('/create'),
            'edit' => EditCoverLetterGeneration::route('/{record}/edit'),
        ];
    }
}

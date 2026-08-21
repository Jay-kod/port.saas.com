<?php

namespace App\Filament\Resources\PortfolioReports\Tables;

use App\Models\PortfolioReport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PortfolioReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profile.full_name')
                    ->label('Reported Profile')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('profile.slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('reason')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'spam', 'scam' => 'danger',
                        'inappropriate', 'copyright' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('details')
                    ->limit(40)
                    ->tooltip(fn (PortfolioReport $record): ?string => $record->details),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'reviewed' => 'success',
                        'dismissed' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('reporter_ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Reported At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('unpublish')
                    ->label('Unpublish Portfolio')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (PortfolioReport $record) {
                        $record->profile?->update(['is_published' => false]);
                        $record->update(['status' => 'reviewed']);

                        Notification::make()
                            ->title('Portfolio Unpublished')
                            ->body('The reported portfolio was taken offline and marked as reviewed.')
                            ->success()
                            ->send();
                    }),
                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-check')
                    ->color('gray')
                    ->action(function (PortfolioReport $record) {
                        $record->update(['status' => 'dismissed']);

                        Notification::make()
                            ->title('Report Dismissed')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

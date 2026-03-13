<?php

namespace App\Filament\Resources\ImportRuns\Tables;

use App\Enums\ImportStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImportRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll()
            ->columns([
                TextColumn::make('original_name')
                    ->label('Soubor')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        if ($state instanceof ImportStatus) {
                            return $state->label();
                        }

                        return (string) $state;
                    })
                    ->color(function ($state): string {
                        if ($state instanceof ImportStatus) {
                            return $state->color();
                        }

                        return 'gray';
                    })
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Celkem')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valid')
                    ->label('Validní')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('invalid')
                    ->label('Nevalidní')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('duplicates')
                    ->label('Duplicitní')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('duration_ms')
                    ->label('Čas')
                    ->formatStateUsing(function (?int $state): string {
                        if (! $state) {
                            return '-';
                        }

                        return number_format($state / 1000, 2, ',', ' ').' s';
                    }),
                TextColumn::make('started_at')
                    ->label('Start')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->label('Konec')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

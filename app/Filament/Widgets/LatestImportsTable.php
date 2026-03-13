<?php

namespace App\Filament\Widgets;

use App\Enums\ImportStatus;
use App\Models\ImportRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestImportsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Poslední importy')
            ->query(fn (): Builder => ImportRun::query()
                ->orderByDesc('started_at')
                ->orderByDesc('created_at')
                ->limit(5))
            ->columns([
                TextColumn::make('original_name')
                    ->label('Soubor')
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
                    }),
                TextColumn::make('total')
                    ->label('Celkem')
                    ->numeric(),
                TextColumn::make('invalid')
                    ->label('Nevalidní')
                    ->numeric(),
                TextColumn::make('duplicates')
                    ->label('Duplicitní')
                    ->numeric(),
                TextColumn::make('finished_at')
                    ->label('Konec')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->paginated(false);
    }
}

<?php

namespace App\Filament\Resources\ImportRuns\Pages;

use App\Filament\Resources\ImportRuns\ImportRunResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImportRuns extends ListRecords
{
    protected static string $resource = ImportRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nový import'),
        ];
    }
}

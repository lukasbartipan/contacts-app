<?php

namespace App\Filament\Resources\ImportRuns\Pages;

use App\Filament\Resources\ImportRuns\ImportRunResource;
use App\Jobs\ProcessContactImport;
use Filament\Resources\Pages\CreateRecord;

class CreateImportRun extends CreateRecord
{
    protected static string $resource = ImportRunResource::class;

    protected function afterCreate(): void
    {
        ProcessContactImport::dispatch($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}

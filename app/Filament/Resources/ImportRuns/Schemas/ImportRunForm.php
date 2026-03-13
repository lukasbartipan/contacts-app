<?php

namespace App\Filament\Resources\ImportRuns\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class ImportRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('stored_path')
                    ->label('XML soubor')
                    ->helperText('Podporovaný formát: XML, UTF-8.')
                    ->disk('local')
                    ->directory('imports')
                    ->preserveFilenames()
                    ->storeFileNamesIn('original_name')
                    ->acceptedFileTypes(['application/xml', 'text/xml'])
                    ->rules(['file', 'mimes:xml'])
                    ->maxSize(51200)
                    ->required(),
            ]);
    }
}

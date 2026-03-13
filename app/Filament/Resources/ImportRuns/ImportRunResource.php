<?php

namespace App\Filament\Resources\ImportRuns;

use App\Filament\Resources\ImportRuns\Pages\CreateImportRun;
use App\Filament\Resources\ImportRuns\Pages\ListImportRuns;
use App\Filament\Resources\ImportRuns\Schemas\ImportRunForm;
use App\Filament\Resources\ImportRuns\Tables\ImportRunsTable;
use App\Models\ImportRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ImportRunResource extends Resource
{
    protected static ?string $model = ImportRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $modelLabel = 'Import';

    protected static ?string $pluralModelLabel = 'Importy';

    protected static ?string $navigationLabel = 'Importy';

    public static function form(Schema $schema): Schema
    {
        return ImportRunForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImportRunsTable::configure($table);
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
            'index' => ListImportRuns::route('/'),
            'create' => CreateImportRun::route('/create'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
}

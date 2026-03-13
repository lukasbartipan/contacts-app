<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll()
            ->columns([
                TextColumn::make('email')
                    ->label('E-mail')
                    ->sortable()
                    ->copyable()
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('Jméno')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Příjmení')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->searchable()
            ->searchPlaceholder('Hledat podle jména, příjmení nebo e-mailu')
            ->searchUsing(function (Builder $query, string $search): void {
                /**
                 * Full-text AND for names (boolean +term*), email domains via LIKE.
                 */
                $terms = preg_split('/\s+/', trim($search)) ?: [];
                $terms = array_values(array_filter($terms));
                /**
                 * Sanitize terms into tokens safe for MySQL boolean full-text.
                 */
                $booleanTerms = array_values(array_filter(array_map(
                    static fn (string $term): string => preg_replace('/[^\pL\pN_]+/u', '', $term) ?? '',
                    $terms
                )));
                $booleanQuery = implode(' ', array_map(
                    static fn (string $term): string => '+'.$term.'*',
                    $booleanTerms
                ));

                $query->where(function (Builder $builder) use ($booleanQuery, $search): void {
                    /**
                     * AND full-text when terms exist; otherwise only email LIKE.
                     */
                    if ($booleanQuery !== '') {
                        $builder->whereFullText(['email', 'first_name', 'last_name'], $booleanQuery, ['mode' => 'boolean']);
                    }

                    $builder->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Contacts\Schemas;

use App\Models\Contact;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->rule('email:rfc')
                    ->rule(static function (string $attribute, mixed $value, Closure $fail): void {
                        if (! Contact::hasFullDomain((string) $value)) {
                            $fail('E-mail musí obsahovat celou doménu (např. .cz).');
                        }
                    })
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('first_name')
                    ->label('Jméno')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label('Příjmení')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}

<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
    ];

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?self $record = null): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if (! Contact::hasFullDomain((string) $value)) {
                        $fail('E-mail musí obsahovat celou doménu (např. .cz).');
                    }
                },
                Rule::unique('contacts', 'email')->ignore($record),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'email.required' => 'E-mail je povinný.',
            'email.email' => 'E-mail musí být ve správném formátu (RFC).',
            'email.unique' => 'Tento e-mail už v databázi existuje.',
            'first_name.required' => 'Jméno je povinné.',
            'last_name.required' => 'Příjmení je povinné.',
        ];
    }

    public static function hasFullDomain(string $email): bool
    {
        $email = trim($email);

        if (! str_contains($email, '@')) {
            return false;
        }

        [$local, $domain] = explode('@', $email, 2);

        if ($local === '' || $domain === '') {
            return false;
        }

        if (str_starts_with($domain, '.') || str_ends_with($domain, '.')) {
            return false;
        }

        return str_contains($domain, '.');
    }
}

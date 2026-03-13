<?php

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

it('creates, updates, and deletes a contact', function () {
    $contact = Contact::factory()->create();

    expect(Contact::query()->count())->toBe(1);

    $contact->update([
        'first_name' => 'Jan',
        'last_name' => 'Novák',
    ]);

    $contact->refresh();

    expect($contact->first_name)->toBe('Jan');
    expect($contact->last_name)->toBe('Novák');

    $contact->delete();

    expect(Contact::query()->count())->toBe(0);
});

it('validates contact data', function (array $payload, array $expectedErrors) {
    $validator = Validator::make($payload, Contact::rules());

    expect($validator->fails())->toBeTrue();
    expect(array_keys($validator->errors()->toArray()))->toEqualCanonicalizing($expectedErrors);
})->with([
    'missing fields' => [
        ['email' => '', 'first_name' => '', 'last_name' => ''],
        ['email', 'first_name', 'last_name'],
    ],
    'invalid email' => [
        ['email' => 'neplatny', 'first_name' => 'Test', 'last_name' => 'User'],
        ['email'],
    ],
    'missing tld' => [
        ['email' => 'nsmith@yahoo', 'first_name' => 'Test', 'last_name' => 'User'],
        ['email'],
    ],
]);

it('rejects duplicate emails', function () {
    Contact::factory()->create(['email' => 'dup@example.com']);

    $validator = Validator::make([
        'email' => 'dup@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
    ], Contact::rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

it('accepts valid contact data', function () {
    $validator = Validator::make([
        'email' => 'valid@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
    ], Contact::rules());

    expect($validator->fails())->toBeFalse();
});

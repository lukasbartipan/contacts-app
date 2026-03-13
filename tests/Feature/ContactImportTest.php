<?php

use App\Enums\ImportStatus;
use App\Jobs\ProcessContactImport;
use App\Models\Contact;
use App\Models\ImportRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('imports contacts from xml and reports results', function () {
    Storage::fake('local');

    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
  <item>
    <email>valid@example.com</email>
    <first_name>John</first_name>
    <last_name>Doe</last_name>
  </item>
  <item>
    <email>invalid</email>
    <first_name>Bad</first_name>
    <last_name>Email</last_name>
  </item>
  <item>
    <email>valid@example.com</email>
    <first_name>Dup</first_name>
    <last_name>Again</last_name>
  </item>
  <item>
    <email>nsmith@yahoo</email>
    <first_name>Missing</first_name>
    <last_name>Tld</last_name>
  </item>
</data>
XML;

    Storage::disk('local')->put('imports/contacts.xml', $xml);

    $importRun = ImportRun::factory()->create([
        'original_name' => 'contacts.xml',
        'stored_path' => 'imports/contacts.xml',
        'status' => ImportStatus::Pending,
        'total' => 0,
        'valid' => 0,
        'invalid' => 0,
        'duplicates' => 0,
    ]);

    ProcessContactImport::dispatchSync($importRun);

    $importRun->refresh();

    expect($importRun->status)->toBe(ImportStatus::Finished);
    expect($importRun->total)->toBe(4);
    expect($importRun->valid)->toBe(2);
    expect($importRun->invalid)->toBe(2);
    expect($importRun->duplicates)->toBe(1);
    expect($importRun->started_at)->not->toBeNull();
    expect($importRun->finished_at)->not->toBeNull();
    expect($importRun->duration_ms)->not->toBeNull();
    expect(Contact::query()->count())->toBe(1);
});

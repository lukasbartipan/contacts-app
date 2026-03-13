<?php

namespace App\Jobs;

use App\Enums\ImportStatus;
use App\Models\Contact;
use App\Models\ImportRun;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use XMLReader;

class ProcessContactImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const BATCH_SIZE = 1000;

    /**
     * Create a new job instance.
     */
    public function __construct(public ImportRun $importRun) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($this->importRun->stored_path)) {
            throw new RuntimeException('Soubor pro import nebyl nalezen.');
        }

        $startedAt = now();

        $this->updateImportRun([
            'status' => ImportStatus::Processing->value,
            'started_at' => $startedAt,
            'error_message' => null,
        ]);

        $emailValidator = new EmailValidator;
        $rfcValidation = new RFCValidation;

        $reader = new XMLReader;

        if (! $reader->open($disk->path($this->importRun->stored_path))) {
            throw new RuntimeException('Soubor pro import nelze otevřít.');
        }

        $total = 0;
        $valid = 0;
        $invalid = 0;
        $inserted = 0;
        $batch = [];

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'item') {
                continue;
            }

            $total++;

            $item = $this->readItem($reader);
            $email = trim($item['email']);
            $firstName = trim($item['first_name']);
            $lastName = trim($item['last_name']);

            if ($email === '' || $firstName === '' || $lastName === '') {
                $invalid++;

                continue;
            }

            if (! $emailValidator->isValid($email, $rfcValidation)) {
                $invalid++;

                continue;
            }

            if (! Contact::hasFullDomain($email)) {
                $invalid++;

                continue;
            }

            $valid++;
            $batch[] = [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'created_at' => $startedAt,
                'updated_at' => $startedAt,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                $inserted += $this->insertBatch($batch);
                $batch = [];

                $this->updateProgress($total, $valid, $invalid, $inserted);
            }
        }

        if ($batch !== []) {
            $inserted += $this->insertBatch($batch);
        }

        $reader->close();

        $duplicates = max($valid - $inserted, 0);

        $this->updateImportRun([
            'status' => ImportStatus::Finished->value,
            'total' => $total,
            'valid' => $valid,
            'invalid' => $invalid,
            'duplicates' => $duplicates,
            'finished_at' => now(),
            'duration_ms' => $startedAt->diffInMilliseconds(now()),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $importRun = ImportRun::query()->find($this->importRun->getKey());

        $this->updateImportRun([
            'status' => ImportStatus::Failed->value,
            'error_message' => $exception->getMessage(),
            'finished_at' => now(),
            'duration_ms' => $importRun?->started_at?->diffInMilliseconds(now()),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $batch
     */
    private function insertBatch(array $batch): int
    {
        return Contact::query()->insertOrIgnore($batch);
    }

    private function updateProgress(int $total, int $valid, int $invalid, int $inserted): void
    {
        $duplicates = max($valid - $inserted, 0);

        $this->updateImportRun([
            'total' => $total,
            'valid' => $valid,
            'invalid' => $invalid,
            'duplicates' => $duplicates,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function updateImportRun(array $payload): void
    {
        ImportRun::query()
            ->whereKey($this->importRun->getKey())
            ->update($payload);
    }

    /**
     * @return array{email: string, first_name: string, last_name: string}
     */
    private function readItem(XMLReader $reader): array
    {
        $email = '';
        $firstName = '';
        $lastName = '';

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->name === 'item') {
                break;
            }

            if ($reader->nodeType !== XMLReader::ELEMENT) {
                continue;
            }

            $field = $reader->name;
            $value = '';

            if ($reader->read() && in_array($reader->nodeType, [XMLReader::TEXT, XMLReader::CDATA], true)) {
                $value = $reader->value;
            }

            if ($field === 'email') {
                $email = $value;
            }

            if ($field === 'first_name') {
                $firstName = $value;
            }

            if ($field === 'last_name') {
                $lastName = $value;
            }
        }

        return [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
    }
}

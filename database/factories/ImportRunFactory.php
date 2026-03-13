<?php

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\ImportRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportRun>
 */
class ImportRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->subMinutes(fake()->numberBetween(1, 120));
        $finishedAt = (clone $startedAt)->addSeconds(fake()->numberBetween(1, 90));

        return [
            'original_name' => 'contacts.xml',
            'stored_path' => 'imports/contacts.xml',
            'status' => ImportStatus::Finished,
            'total' => 100,
            'valid' => 95,
            'invalid' => 5,
            'duplicates' => 3,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'duration_ms' => $finishedAt->diffInMilliseconds($startedAt),
        ];
    }
}

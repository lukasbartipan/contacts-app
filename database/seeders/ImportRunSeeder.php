<?php

namespace Database\Seeders;

use App\Models\ImportRun;
use Illuminate\Database\Seeder;

class ImportRunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ImportRun::factory()->count(3)->create();
    }
}

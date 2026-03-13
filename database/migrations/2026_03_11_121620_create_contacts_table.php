<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::create('contacts', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamps();

            if ($driver === 'mysql') {
                $table->fullText(['email', 'first_name', 'last_name']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};

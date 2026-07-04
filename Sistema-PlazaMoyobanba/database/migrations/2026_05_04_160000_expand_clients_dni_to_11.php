<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients')) {
            DB::statement('ALTER TABLE clients ALTER COLUMN dni TYPE VARCHAR(11)');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clients')) {
            DB::statement('ALTER TABLE clients ALTER COLUMN dni TYPE VARCHAR(8)');
        }
    }
};


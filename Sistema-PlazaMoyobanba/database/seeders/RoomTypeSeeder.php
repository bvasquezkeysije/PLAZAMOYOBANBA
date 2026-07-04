<?php

namespace Database\Seeders;

use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Simple', 'Matrimonial', 'Doble', 'Suite'];

        foreach ($types as $name) {
            RoomType::firstOrCreate(
                ['name' => $name],
                ['code' => $this->codeFromName($name), 'active' => true]
            );
        }
    }

    private function codeFromName(string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name) ?: 'TIP', 0, 4));
        $suffix = strtoupper(substr(uniqid(), -3));

        return 'TIP-' . str_pad($base, 4, 'X') . '-' . $suffix;
    }
}


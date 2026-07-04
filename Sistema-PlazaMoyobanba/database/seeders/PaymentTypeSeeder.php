<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Efectivo', 'Yape', 'Plin', 'Transferencia'] as $name) {
            PaymentType::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}

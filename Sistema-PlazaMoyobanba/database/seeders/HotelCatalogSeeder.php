<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Product;
use App\Models\Room;
use Illuminate\Database\Seeder;

class HotelCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $floorNames = [
            1 => 'Primer piso',
            2 => 'Segundo piso',
            3 => 'Tercer piso',
            4 => 'Cuarto piso',
            5 => 'Quinto piso',
        ];

        $floorsByNumber = [];
        foreach ($floorNames as $number => $name) {
            $floorsByNumber[$number] = Floor::updateOrCreate(
                ['number' => $number],
                ['code' => sprintf('PIS-%03d', $number), 'name' => $name]
            );
        }

        // 6 habitaciones por piso, variando tipos y excluyendo Suite (TIP-SUIT-D91).
        $roomTypes = [
            ['name' => 'Simple', 'hourly' => 25, 'daily' => 120],
            ['name' => 'Doble', 'hourly' => 35, 'daily' => 160],
            ['name' => 'Matrimonial', 'hourly' => 45, 'daily' => 220],
        ];

        foreach ($floorsByNumber as $floorNumber => $floor) {
            for ($i = 1; $i <= 6; $i++) {
                $roomNumber = (string) ($floorNumber * 100 + $i);
                $code = 'HAB-' . $roomNumber;
                $typeConfig = $roomTypes[($i - 1) % count($roomTypes)];

                Room::updateOrCreate(
                    ['room_number' => $roomNumber],
                    [
                        'code' => $code,
                        'floor_id' => $floor->id,
                        'type' => $typeConfig['name'],
                        'hourly_rate' => $typeConfig['hourly'],
                        'daily_rate' => $typeConfig['daily'],
                        'active' => true,
                    ]
                );
            }
        }

        Product::updateOrCreate(['name' => 'Coca Cola'], ['code' => 'PRO-001', 'category' => 'Bebidas', 'price' => 6.00, 'stock' => 100, 'active' => true]);
        Product::updateOrCreate(['name' => 'Papel higienico'], ['code' => 'PRO-002', 'category' => 'Higiene', 'price' => 3.50, 'stock' => 80, 'active' => true]);
        Product::updateOrCreate(['name' => 'Snack mixto'], ['code' => 'PRO-003', 'category' => 'Snacks', 'price' => 4.50, 'stock' => 120, 'active' => true]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndAdminSeeder::class,
            WorkersSeeder::class,
            HotelCatalogSeeder::class,
            RetailProductsSeeder::class,
            DemoClientsSeeder::class,
            ProductCategorySeeder::class,
            RoomTypeSeeder::class,
            PaymentTypeSeeder::class,
            HotelSalesSeeder::class,
        ]);
    }
}

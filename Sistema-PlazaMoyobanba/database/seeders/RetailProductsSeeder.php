<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class RetailProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['code' => 'PRO-004', 'name' => 'Agua San Luis 625ml', 'category' => 'Bebidas', 'price' => 2.50, 'stock' => 120],
            ['code' => 'PRO-005', 'name' => 'Agua Cielo 625ml', 'category' => 'Bebidas', 'price' => 2.50, 'stock' => 110],
            ['code' => 'PRO-006', 'name' => 'Inca Kola 500ml', 'category' => 'Bebidas', 'price' => 4.50, 'stock' => 90],
            ['code' => 'PRO-007', 'name' => 'Coca Cola 500ml', 'category' => 'Bebidas', 'price' => 4.50, 'stock' => 90],
            ['code' => 'PRO-008', 'name' => 'Sprite 500ml', 'category' => 'Bebidas', 'price' => 4.50, 'stock' => 70],
            ['code' => 'PRO-009', 'name' => 'Fanta Naranja 500ml', 'category' => 'Bebidas', 'price' => 4.50, 'stock' => 70],
            ['code' => 'PRO-010', 'name' => 'Red Bull 250ml', 'category' => 'Bebidas', 'price' => 8.50, 'stock' => 45],
            ['code' => 'PRO-011', 'name' => 'Cerveza Pilsen 620ml', 'category' => 'Bebidas', 'price' => 8.00, 'stock' => 80],
            ['code' => 'PRO-012', 'name' => 'Cerveza Cusquena Trigo 330ml', 'category' => 'Bebidas', 'price' => 7.50, 'stock' => 60],
            ['code' => 'PRO-013', 'name' => 'Jugo Frugos Durazno 1L', 'category' => 'Bebidas', 'price' => 7.00, 'stock' => 40],

            ['code' => 'PRO-014', 'name' => 'Papas Lays Clasicas 45g', 'category' => 'Snacks', 'price' => 3.50, 'stock' => 140],
            ['code' => 'PRO-015', 'name' => 'Papas Lays Onduladas 45g', 'category' => 'Snacks', 'price' => 3.50, 'stock' => 120],
            ['code' => 'PRO-016', 'name' => 'Doritos Queso 40g', 'category' => 'Snacks', 'price' => 3.80, 'stock' => 110],
            ['code' => 'PRO-017', 'name' => 'Chizitos Bolsa 36g', 'category' => 'Snacks', 'price' => 2.80, 'stock' => 130],
            ['code' => 'PRO-018', 'name' => 'Mani Salado 100g', 'category' => 'Snacks', 'price' => 3.00, 'stock' => 100],
            ['code' => 'PRO-019', 'name' => 'Galleta Oreo 6 unid', 'category' => 'Snacks', 'price' => 2.50, 'stock' => 160],
            ['code' => 'PRO-020', 'name' => 'Galleta Soda Field 6 unid', 'category' => 'Snacks', 'price' => 2.00, 'stock' => 150],
            ['code' => 'PRO-021', 'name' => 'Chocolate Sublime Clasico', 'category' => 'Snacks', 'price' => 2.50, 'stock' => 170],
            ['code' => 'PRO-022', 'name' => 'Chocolate Princesa', 'category' => 'Snacks', 'price' => 2.00, 'stock' => 150],
            ['code' => 'PRO-023', 'name' => 'Caramelo Halls Menta', 'category' => 'Snacks', 'price' => 2.00, 'stock' => 130],

            ['code' => 'PRO-024', 'name' => 'Papel Higienico Elite x4', 'category' => 'Higiene', 'price' => 12.00, 'stock' => 70],
            ['code' => 'PRO-025', 'name' => 'Papel Higienico Suelto', 'category' => 'Higiene', 'price' => 3.50, 'stock' => 140],
            ['code' => 'PRO-026', 'name' => 'Jabon Bolivar 190g', 'category' => 'Higiene', 'price' => 3.00, 'stock' => 100],
            ['code' => 'PRO-027', 'name' => 'Jabon Protex 110g', 'category' => 'Higiene', 'price' => 4.50, 'stock' => 85],
            ['code' => 'PRO-028', 'name' => 'Shampoo Sachet Sedal', 'category' => 'Higiene', 'price' => 1.50, 'stock' => 180],
            ['code' => 'PRO-029', 'name' => 'Acondicionador Sachet Sedal', 'category' => 'Higiene', 'price' => 1.50, 'stock' => 160],
            ['code' => 'PRO-030', 'name' => 'Cepillo Dental Oral-B', 'category' => 'Higiene', 'price' => 4.00, 'stock' => 90],
            ['code' => 'PRO-031', 'name' => 'Pasta Dental Kolynos 90g', 'category' => 'Higiene', 'price' => 5.50, 'stock' => 80],
            ['code' => 'PRO-032', 'name' => 'Desodorante Nivea Men 150ml', 'category' => 'Higiene', 'price' => 14.00, 'stock' => 40],
            ['code' => 'PRO-033', 'name' => 'Toallas Humedas x20', 'category' => 'Higiene', 'price' => 6.50, 'stock' => 60],

            ['code' => 'PRO-034', 'name' => 'Preservativos Durex x3', 'category' => 'Cuidado Personal', 'price' => 14.00, 'stock' => 35],
            ['code' => 'PRO-035', 'name' => 'Preservativos Piel x3', 'category' => 'Cuidado Personal', 'price' => 10.00, 'stock' => 45],
            ['code' => 'PRO-036', 'name' => 'Lubricante Intimo 50ml', 'category' => 'Cuidado Personal', 'price' => 16.00, 'stock' => 25],
            ['code' => 'PRO-037', 'name' => 'Toalla Sanitaria x10', 'category' => 'Cuidado Personal', 'price' => 8.50, 'stock' => 50],
            ['code' => 'PRO-038', 'name' => 'Maquina de Afeitar Desechable x2', 'category' => 'Cuidado Personal', 'price' => 6.00, 'stock' => 70],

            ['code' => 'PRO-039', 'name' => 'Detergente Bolivar 500g', 'category' => 'Limpieza', 'price' => 5.50, 'stock' => 65],
            ['code' => 'PRO-040', 'name' => 'Lejia Sapolio 1L', 'category' => 'Limpieza', 'price' => 4.00, 'stock' => 60],
            ['code' => 'PRO-041', 'name' => 'Desinfectante Poett 900ml', 'category' => 'Limpieza', 'price' => 7.50, 'stock' => 55],
            ['code' => 'PRO-042', 'name' => 'Ambientador Glade 360ml', 'category' => 'Limpieza', 'price' => 12.00, 'stock' => 35],
            ['code' => 'PRO-043', 'name' => 'Bolsa de Basura x10', 'category' => 'Limpieza', 'price' => 4.50, 'stock' => 80],

            ['code' => 'PRO-044', 'name' => 'Cuaderno A5 80 hojas', 'category' => 'Utiles', 'price' => 6.00, 'stock' => 50],
            ['code' => 'PRO-045', 'name' => 'Lapicero Pilot Fino', 'category' => 'Utiles', 'price' => 2.00, 'stock' => 120],
            ['code' => 'PRO-046', 'name' => 'Lapiz Mongol HB', 'category' => 'Utiles', 'price' => 1.20, 'stock' => 150],
            ['code' => 'PRO-047', 'name' => 'Resaltador Stabilo', 'category' => 'Utiles', 'price' => 3.50, 'stock' => 70],
            ['code' => 'PRO-048', 'name' => 'Cinta Adhesiva Transparente', 'category' => 'Utiles', 'price' => 3.00, 'stock' => 60],

            ['code' => 'PRO-049', 'name' => 'Cable USB Tipo C 1m', 'category' => 'Accesorios', 'price' => 12.00, 'stock' => 40],
            ['code' => 'PRO-050', 'name' => 'Cargador USB 20W', 'category' => 'Accesorios', 'price' => 30.00, 'stock' => 25],
            ['code' => 'PRO-051', 'name' => 'Audifonos In-Ear Basicos', 'category' => 'Accesorios', 'price' => 15.00, 'stock' => 30],
            ['code' => 'PRO-052', 'name' => 'Pilas AA x2', 'category' => 'Accesorios', 'price' => 7.00, 'stock' => 65],
            ['code' => 'PRO-053', 'name' => 'Pilas AAA x2', 'category' => 'Accesorios', 'price' => 7.00, 'stock' => 65],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['code' => $product['code']],
                [
                    'name' => $product['name'],
                    'category' => $product['category'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'active' => true,
                ]
            );
        }
    }
}

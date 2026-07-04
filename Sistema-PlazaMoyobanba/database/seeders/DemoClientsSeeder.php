<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class DemoClientsSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['code' => 'CLI-0001', 'dni' => '45879632', 'full_name' => 'Juan Carlos Rojas Diaz', 'email' => 'juan.rojas@mail.com', 'phone' => '987654321', 'active' => true],
            ['code' => 'CLI-0002', 'dni' => '71345682', 'full_name' => 'Maria Fernanda Soto Ruiz', 'email' => 'maria.soto@mail.com', 'phone' => '956321478', 'active' => true],
            ['code' => 'CLI-0003', 'dni' => '60231459', 'full_name' => 'Luis Alberto Cueva Ramos', 'email' => 'luis.cueva@mail.com', 'phone' => '945632178', 'active' => true],
            ['code' => 'CLI-0004', 'dni' => '75963124', 'full_name' => 'Rosa Elena Paredes Mena', 'email' => 'rosa.paredes@mail.com', 'phone' => '989741236', 'active' => true],
        ];

        $firstNames = [
            'Jorge', 'Carlos', 'Luis', 'Pedro', 'Andres', 'Diego', 'Marco', 'Jose', 'Alex', 'Renzo',
            'Kevin', 'Bruno', 'Cesar', 'Miguel', 'Adrian', 'Daniel', 'Ricardo', 'Samuel', 'Victor', 'Franco',
            'Valeria', 'Camila', 'Lucia', 'Daniela', 'Andrea', 'Fiorella', 'Mariana', 'Paola', 'Rocio', 'Yessenia',
            'Karla', 'Angie', 'Milagros', 'Nathaly', 'Sofia', 'Ariana', 'Ximena', 'Noelia', 'Tatiana', 'Claudia',
        ];

        $middleNames = [
            'Alberto', 'Enrique', 'Antonio', 'David', 'Jesus', 'Manuel', 'Elena', 'Fernanda', 'Patricia', 'Beatriz',
            'Javier', 'Sebastian', 'Ignacio', 'Martin', 'Gabriel', 'Alejandro', 'Cristian', 'Diana', 'Lorena', 'Estefania',
        ];

        $lastNames = [
            'Vasquez', 'Garcia', 'Torres', 'Flores', 'Mendoza', 'Castillo', 'Chavez', 'Diaz', 'Sanchez', 'Rodriguez',
            'Fernandez', 'Ramos', 'Lopez', 'Vera', 'Huaman', 'Silva', 'Paredes', 'Ruiz', 'Campos', 'Cruz',
            'Reyes', 'Mora', 'Pinto', 'Navarro', 'Tello', 'Quispe', 'Aguirre', 'Salazar', 'Espinoza', 'Palacios',
        ];

        for ($i = 5; $i <= 70; $i++) {
            $first = $firstNames[($i * 3) % count($firstNames)];
            $middle = $middleNames[($i * 5) % count($middleNames)];
            $last1 = $lastNames[($i * 7) % count($lastNames)];
            $last2 = $lastNames[($i * 11 + 3) % count($lastNames)];

            if ($last1 === $last2) {
                $last2 = $lastNames[($i * 13 + 1) % count($lastNames)];
            }

            $fullName = "$first $middle $last1 $last2";
            $emailSlug = strtolower($first . '.' . $last1 . $i);

            $clients[] = [
                'code' => sprintf('CLI-%04d', $i),
                'dni' => (string) (42000000 + ($i * 173)),
                'full_name' => $fullName,
                'email' => $emailSlug . '@mail.com',
                'phone' => (string) (900000000 + ($i * 1379)),
                'active' => true,
            ];
        }

        foreach ($clients as $data) {
            Client::updateOrCreate(
                ['code' => $data['code']],
                [
                    'dni' => $data['dni'],
                    'full_name' => $data['full_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'active' => $data['active'],
                ]
            );
        }
    }
}

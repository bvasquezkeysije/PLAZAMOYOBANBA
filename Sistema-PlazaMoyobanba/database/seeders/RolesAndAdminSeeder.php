<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::updateOrCreate(['name' => 'admin'], ['is_active' => true]);
        $gerenteRole = Role::updateOrCreate(['name' => 'gerente'], ['is_active' => true]);
        $recepcionistaRole = Role::updateOrCreate(['name' => 'recepcionista'], ['is_active' => true]);
        $contadorRole = Role::updateOrCreate(['name' => 'contador'], ['is_active' => true]);
        $limpiezaRole = Role::updateOrCreate(['name' => 'limpieza'], ['is_active' => false]);

        $permissions = [
            'dashboard.view',
            'users.view',
            'users.create',
            'users.edit',
            'users.deactivate',
            'roles.manage',
            'clients.view',
            'clients.create',
            'clients.edit',
            'products.view',
            'products.create',
            'products.edit',
            'rooms.view',
            'rooms.create',
            'rooms.edit',
            'floors.manage',
            'sales.view',
            'sales.create',
            'sales.edit',
            'guests.view',
            'guests.create',
            'guests.print',
            'reports.view',
            'categories.manage',
            'roomtypes.manage',
            'paymenttypes.manage',
            'settings.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole->syncPermissions($permissions);
        $gerenteRole->syncPermissions([
            'dashboard.view',
            'users.view',
            'clients.view',
            'clients.create',
            'clients.edit',
            'products.view',
            'products.create',
            'products.edit',
            'categories.manage',
            'rooms.view',
            'rooms.create',
            'rooms.edit',
            'floors.manage',
            'roomtypes.manage',
            'sales.view',
            'sales.create',
            'sales.edit',
            'guests.view',
            'guests.create',
            'guests.print',
            'reports.view',
            'paymenttypes.manage',
            'settings.view',
        ]);
        $recepcionistaRole->syncPermissions([
            'dashboard.view',
            'clients.view',
            'clients.create',
            'clients.edit',
            'products.view',
            'rooms.view',
            'sales.view',
            'sales.create',
            'guests.view',
            'guests.create',
            'guests.print',
        ]);
        $contadorRole->syncPermissions([
            'dashboard.view',
            'sales.view',
            'reports.view',
            'guests.view',
            'settings.view',
        ]);
        $limpiezaRole->syncPermissions([]);

        $adminUsers = [
            [
                'name' => 'KEYSI JEANPIERRE BARDALES VASQUEZ',
                'username' => 'bvasquezkeysije',
                'email' => 'bvasquezkeysije@uss.edu.pe',
                'password' => '76636255',
            ],
            [
                'name' => 'DELGADO GARCIA BRIGGITTE LUCERO',
                'username' => 'dgarciabriggitl',
                'email' => 'dgarciabriggitl@uss.edu.pe',
                'password' => '76465678',
            ],
            [
                'name' => 'VASQUEZ QUISPE JORGE TOMAS',
                'username' => 'vquispejorgetom',
                'email' => 'vquispejorgetom@uss.edu.pe',
                'password' => '72838203',
            ],
            [
                'name' => 'CAPITAN LEON GRABIEL ALEXANDER',
                'username' => 'cleonalexandgra',
                'email' => 'cleonalexandgra@uss.edu.pe',
                'password' => '73149801',
            ],
            [
                'name' => 'ADMINISTRADOR',
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => '123',
            ],
        ];

        foreach ($adminUsers as $adminData) {
            $adminUser = User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'username' => $adminData['username'],
                    'password' => Hash::make($adminData['password']),
                    'is_active' => true,
                ]
            );

            $adminUser->syncRoles([$adminRole->name]);
        }

        $roleUsers = [
            [
                'role' => $gerenteRole,
                'name' => 'MELISSA FERNANDA RUIZ CAMPOS',
                'username' => 'mruizcampos',
                'email' => 'mruizcampos@plazamoyobanba.com',
                'password' => 'Gerente2026!',
            ],
            [
                'role' => $contadorRole,
                'name' => 'EDUARDO ANTONIO SALAZAR VEGA',
                'username' => 'esalazarvega',
                'email' => 'esalazarvega@plazamoyobanba.com',
                'password' => 'Contador2026!',
            ],
            [
                'role' => $recepcionistaRole,
                'name' => 'KARLA NOEMI PEREZ HUAMAN',
                'username' => 'kperezhuaman',
                'email' => 'kperezhuaman@plazamoyobanba.com',
                'password' => 'Recepcion2026!',
            ],
        ];

        foreach ($roleUsers as $staffData) {
            $staffUser = User::updateOrCreate(
                ['email' => $staffData['email']],
                [
                    'name' => $staffData['name'],
                    'username' => $staffData['username'],
                    'password' => Hash::make($staffData['password']),
                    'is_active' => true,
                ]
            );

            $staffUser->syncRoles([$staffData['role']->name]);
        }
    }
}

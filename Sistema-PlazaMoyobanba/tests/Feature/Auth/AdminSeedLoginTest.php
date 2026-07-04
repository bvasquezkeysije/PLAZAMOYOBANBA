<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeedLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admins_can_login_with_email_and_password(): void
    {
        $this->seed(RolesAndAdminSeeder::class);

        $credentials = [
            ['login' => 'bvasquezkeysije@uss.edu.pe', 'password' => '76636255'],
            ['login' => 'dgarciabriggitl@uss.edu.pe', 'password' => '76465678'],
            ['login' => 'vquispejorgetom@uss.edu.pe', 'password' => '72838203'],
            ['login' => 'cleonalexandgra@uss.edu.pe', 'password' => '73149801'],
        ];

        foreach ($credentials as $credential) {
            $this->post('/login', $credential)->assertRedirect(route('dashboard', absolute: false));
            $this->assertAuthenticated();
            $this->post('/logout');
            $this->assertGuest();
        }
    }

    public function test_seeded_admins_can_login_with_username_and_password(): void
    {
        $this->seed(RolesAndAdminSeeder::class);

        $credentials = [
            ['login' => 'bvasquezkeysije', 'password' => '76636255'],
            ['login' => 'dgarciabriggitl', 'password' => '76465678'],
            ['login' => 'vquispejorgetom', 'password' => '72838203'],
            ['login' => 'cleonalexandgra', 'password' => '73149801'],
        ];

        foreach ($credentials as $credential) {
            $this->post('/login', $credential)->assertRedirect(route('dashboard', absolute: false));
            $this->assertAuthenticated();
            $this->post('/logout');
            $this->assertGuest();
        }
    }
}

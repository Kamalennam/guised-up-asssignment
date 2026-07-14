<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Tests\Concerns\RefreshPostgresDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshPostgresDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runDatabaseSeeder(new DemoSeeder);
    }

    public function test_demo_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'kamal@guisedup.test',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'username', 'name', 'email', 'avatar_url'],
                ],
            ])
            ->assertJsonPath('data.user.username', 'kamal')
            ->assertJsonPath('data.user.email', 'kamal@guisedup.test');

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_all_demo_users_can_authenticate(): void
    {
        foreach (['kamal@guisedup.test', 'kishore@guisedup.test', 'venu@guisedup.test'] as $email) {
            $response = $this->postJson('/api/auth/login', [
                'email' => $email,
                'password' => 'password',
            ]);

            $response->assertOk();
            $this->assertNotEmpty($response->json('data.token'));
        }
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'kamal@guisedup.test',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'unknown@guisedup.test',
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}

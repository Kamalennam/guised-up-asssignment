<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\RefreshPostgresDatabase;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshPostgresDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runDatabaseSeeder(new DemoSeeder);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = User::query()->where('email', 'kamal@guisedup.test')->firstOrFail();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.username', 'kamal')
            ->assertJsonPath('data.email', 'kamal@guisedup.test')
            ->assertJsonMissingPath('data.password');
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertUnauthorized();
    }

    public function test_me_rejects_invalid_token(): void
    {
        $response = $this->getJson('/api/auth/me', [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertUnauthorized();
    }
}

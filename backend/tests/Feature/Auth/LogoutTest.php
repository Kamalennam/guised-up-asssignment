<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\Concerns\RefreshPostgresDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshPostgresDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runDatabaseSeeder(new DemoSeeder);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::query()->where('email', 'kamal@guisedup.test')->firstOrFail();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertNull(PersonalAccessToken::findToken($token));
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertUnauthorized();
    }

    public function test_token_cannot_be_reused_after_logout(): void
    {
        $user = User::query()->where('email', 'kishore@guisedup.test')->firstOrFail();
        $token = $user->createToken('api')->plainTextToken;

        $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk();

        $this->assertNull(PersonalAccessToken::findToken($token));

        $this->app['auth']->forgetGuards();

        $this->getJson('/api/auth/me', [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnauthorized();
    }
}

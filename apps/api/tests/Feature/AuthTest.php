<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Iam\Database\Seeders\PermissionSeeder;
use App\Modules\Iam\Database\Seeders\RoleSeeder;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Database\Seeders\InstitutionSeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

final class AuthTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InstitutionSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(DemoUserSeeder::class);
    }

    public function test_api_root_points_to_first_party_portals(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertJsonPath('portals.admin', 'http://localhost:3005/login');
        $response->assertJsonPath('portals.student', 'http://localhost:3002/login');
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'healthy',
            'system' => 'MEMA ERP API',
        ]);
    }

    public function test_admin_can_authenticate_via_api_with_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'admin@mema.ac.ke',
            'password' => 'password123',
            'device_name' => 'test-runner',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
            'user' => [
                'id',
                'email',
                'username',
                'person',
                'institution',
                'roles',
                'permissions',
            ],
        ]);

        $this->assertDatabaseHas('iam.login_attempts', [
            'email' => 'admin@mema.ac.ke',
            'succeeded' => true,
        ]);
    }

    public function test_admin_can_authenticate_via_api_with_username(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'admin',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('user.email', 'admin@mema.ac.ke');
    }

    public function test_invalid_credentials_are_rejected_and_logged(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'admin@mema.ac.ke',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('iam.login_attempts', [
            'email' => 'admin@mema.ac.ke',
            'succeeded' => false,
            'failure_reason' => 'INVALID_PASSWORD',
        ]);
    }

    public function test_authenticated_user_can_fetch_me_profile(): void
    {
        $user = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $response->assertJsonPath('user.email', 'admin@mema.ac.ke');
        $response->assertJsonPath('user.roles.0.role_code', 'system-admin');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logged out successfully.']);
    }
}

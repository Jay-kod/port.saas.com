<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardUserMenuAndLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_dashboard_has_anti_cache_headers(): void
    {
        $user = User::factory()->create(['name' => 'Jane Developer']);
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'name' => 'Jane Workspace',
            'plan_slug' => 'free',
        ]);
        Profile::create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'jane-dev',
            'full_name' => 'Jane Developer',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->hasCacheControlDirective('no-cache'));
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
        $this->assertTrue($response->headers->hasCacheControlDirective('must-revalidate'));
        $response->assertHeader('Pragma', 'no-cache');
    }

    public function test_dashboard_renders_initials_profile_settings_and_logout_modal(): void
    {
        $user = User::factory()->create([
            'name' => 'Alex Rivera',
            'email' => 'alex@example.com',
        ]);
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'name' => 'Alex Workspace',
            'plan_slug' => 'free',
        ]);
        Profile::create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'alex-rivera',
            'full_name' => 'Alex Rivera',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        // Initials for Alex Rivera -> AR
        $response->assertSee('AR');
        $response->assertSee('Alex Rivera');
        $response->assertSee('Profile');
        $response->assertSee('Settings');
        $response->assertSee('Sign Out');
        $response->assertSee('Confirm Sign Out');
        $response->assertSee('Are you sure you want to log out?');
    }

    public function test_super_admin_dashboard_renders_initials_and_root_logout_modal(): void
    {
        $admin = User::factory()->create([
            'name' => 'Sam Root',
            'email' => 'root@example.com',
            'is_super_admin' => true,
        ]);
        $account = Account::factory()->create([
            'owner_user_id' => $admin->id,
            'name' => 'Admin Workspace',
            'plan_slug' => 'agency',
        ]);
        Profile::create([
            'account_id' => $account->id,
            'user_id' => $admin->id,
            'slug' => 'sam-root',
            'full_name' => 'Sam Root',
            'is_published' => true,
        ]);

        $response = $this->actingAs($admin)->get('/super-admin');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->hasCacheControlDirective('no-cache'));
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
        $this->assertTrue($response->headers->hasCacheControlDirective('must-revalidate'));
        $response->assertSee('SR');
        $response->assertSee('Sam Root');
        $response->assertSee('Terminate Root Session?');
        $response->assertSee('Terminate');
    }

    public function test_logout_terminates_session_and_blocks_subsequent_dashboard_access(): void
    {
        $user = User::factory()->create(['name' => 'Morgan Reed']);
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'name' => 'Morgan Workspace',
            'plan_slug' => 'pro',
        ]);
        Profile::create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'morgan-reed',
            'full_name' => 'Morgan Reed',
            'is_published' => true,
        ]);

        // 1. Authenticate and access dashboard
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        // 2. Perform deliberate logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/admin/login');
        $this->assertGuest();

        // 3. Attempt to navigate back to dashboard without active session
        $backResponse = $this->get('/dashboard');
        $backResponse->assertRedirect('/admin/login');

        // 4. Attempt to navigate back to agency without active session
        $agencyResponse = $this->get('/agency');
        $agencyResponse->assertRedirect('/admin/login');

        // 5. Attempt to navigate to super-admin without active session
        $superAdminResponse = $this->get('/super-admin');
        $superAdminResponse->assertRedirect('/admin/login');
    }
}

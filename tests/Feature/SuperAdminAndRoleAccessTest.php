<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\PortfolioReport;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAndRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_master_control_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'is_super_admin' => true,
        ]);

        $account = Account::factory()->create(['owner_user_id' => $superAdmin->id]);
        $superAdmin->accounts()->attach($account);

        $response = $this->actingAs($superAdmin)->get('/super-admin');

        $response->assertStatus(200);
        $response->assertSee('SUPER ADMIN');
        $response->assertSee('Global Platform Operations');
    }

    public function test_regular_user_is_strictly_forbidden_from_super_admin(): void
    {
        $regularUser = User::factory()->create([
            'is_super_admin' => false,
        ]);

        $account = Account::factory()->create(['owner_user_id' => $regularUser->id]);
        $regularUser->accounts()->attach($account);

        $response = $this->actingAs($regularUser)->get('/super-admin');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_visitor_is_redirected_to_login_from_super_admin(): void
    {
        $response = $this->get('/super-admin');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    public function test_single_user_can_access_multiple_dashboards_concurrently(): void
    {
        $superAdmin = User::factory()->create([
            'is_super_admin' => true,
        ]);

        $account = Account::factory()->create([
            'owner_user_id' => $superAdmin->id,
            'plan_slug' => 'agency',
        ]);
        $superAdmin->accounts()->attach($account);

        Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $superAdmin->id,
            'slug' => 'commander',
            'is_published' => true,
        ]);

        // Request 1: User Dashboard
        $dashboardResponse = $this->actingAs($superAdmin)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('AI Portfolio Engine Active');

        // Request 2: Agency Hub
        $agencyResponse = $this->actingAs($superAdmin)->get('/agency');
        $agencyResponse->assertStatus(200);
        $agencyResponse->assertSee('Agency Client Hub');

        // Request 3: Super Admin Master Control
        $superAdminResponse = $this->actingAs($superAdmin)->get('/super-admin');
        $superAdminResponse->assertStatus(200);
        $superAdminResponse->assertSee('SUPER ADMIN MASTER CONTROL');
    }

    public function test_super_admin_can_review_and_resolve_portfolio_reports(): void
    {
        $superAdmin = User::factory()->create([
            'is_super_admin' => true,
        ]);

        $account = Account::factory()->create(['owner_user_id' => $superAdmin->id]);
        $superAdmin->accounts()->attach($account);

        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $superAdmin->id,
            'slug' => 'flagged-site',
        ]);

        $report = PortfolioReport::create([
            'profile_id' => $profile->id,
            'reporter_ip' => '127.0.0.1',
            'reason' => 'spam',
            'details' => 'Commercial spam content detected',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin');

        $response->assertStatus(200);
        $response->assertSee('Commercial spam content detected');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\PortfolioReport;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SuperAdminDashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularUser;
    protected Account $account;
    protected Profile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Root Super Admin',
            'is_super_admin' => true,
        ]);

        $this->regularUser = User::factory()->create([
            'email' => 'developer@example.com',
            'name' => 'Regular Developer',
            'is_super_admin' => false,
        ]);

        $this->account = Account::factory()->create([
            'name' => 'Starlight Digital',
            'owner_user_id' => $this->regularUser->id,
            'plan_slug' => 'free',
            'ai_generations_used_current_period' => 3,
        ]);

        $this->profile = Profile::create([
            'account_id' => $this->account->id,
            'user_id' => $this->regularUser->id,
            'slug' => 'alex-hunter',
            'full_name' => 'Alex Hunter',
            'headline' => 'Full-Stack Developer',
            'is_published' => true,
            'is_discoverable' => true,
            'theme_mode_default' => 'dark',
        ]);
    }

    public function test_unauthenticated_guests_are_redirected_to_super_admin_login_for_all_7_routes(): void
    {
        $routes = [
            'super-admin.dashboard',
            'super-admin.tenants',
            'super-admin.users',
            'super-admin.portfolios',
            'super-admin.reports',
            'super-admin.ai-telemetry',
            'super-admin.system',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertRedirect(route('super-admin.login'));
        }
    }

    public function test_non_super_admin_users_are_forbidden_from_all_7_routes(): void
    {
        $routes = [
            'super-admin.dashboard',
            'super-admin.tenants',
            'super-admin.users',
            'super-admin.portfolios',
            'super-admin.reports',
            'super-admin.ai-telemetry',
            'super-admin.system',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($this->regularUser)->get(route($routeName));
            $response->assertStatus(403);
        }
    }

    public function test_authenticated_super_admin_can_access_all_7_routes_with_http_200(): void
    {
        $routes = [
            'super-admin.dashboard' => 'Super Admin Master Control',
            'super-admin.tenants' => 'Tenant Accounts Manager',
            'super-admin.users' => 'Users & Role Privileges',
            'super-admin.portfolios' => 'Global Portfolios Directory',
            'super-admin.reports' => 'Portfolio Moderation & Abuse Reports',
            'super-admin.ai-telemetry' => 'Platform AI & LLM Telemetry Center',
            'super-admin.system' => 'System Diagnostics & Operations',
        ];

        foreach ($routes as $routeName => $needle) {
            $response = $this->actingAs($this->superAdmin)->get(route($routeName));
            $response->assertOk();
            $response->assertSee($needle, false);
        }
    }

    public function test_super_admin_can_override_tenant_plan_and_reset_ai_quota(): void
    {
        Volt::actingAs($this->superAdmin)
            ->test('super-admin.tenants')
            ->call('openEditPlanModal', $this->account->id)
            ->set('overridePlanSlug', 'agency')
            ->call('updatePlan')
            ->assertHasNoErrors();

        $this->assertEquals('agency', $this->account->fresh()->plan_slug);

        // Reset AI Quota
        Volt::actingAs($this->superAdmin)
            ->test('super-admin.tenants')
            ->call('resetAiUsage', $this->account->id)
            ->assertHasNoErrors();

        $this->assertEquals(0, $this->account->fresh()->ai_generations_used_current_period);
    }

    public function test_super_admin_can_promote_and_demote_users_with_self_lock(): void
    {
        // Promote regular user to super admin
        Volt::actingAs($this->superAdmin)
            ->test('super-admin.users')
            ->call('toggleSuperAdmin', $this->regularUser->id)
            ->assertHasNoErrors();

        $this->assertTrue((bool)$this->regularUser->fresh()->is_super_admin);

        // Try to demote oneself (should be blocked by safety check)
        Volt::actingAs($this->superAdmin)
            ->test('super-admin.users')
            ->call('toggleSuperAdmin', $this->superAdmin->id)
            ->assertSee('Security restriction');

        $this->assertTrue((bool)$this->superAdmin->fresh()->is_super_admin);
    }

    public function test_super_admin_can_toggle_portfolio_publishing_and_seo(): void
    {
        Volt::actingAs($this->superAdmin)
            ->test('super-admin.portfolios')
            ->call('togglePublish', $this->profile->id)
            ->assertHasNoErrors();

        $this->assertFalse((bool)$this->profile->fresh()->is_published);

        Volt::actingAs($this->superAdmin)
            ->test('super-admin.portfolios')
            ->call('toggleDiscoverable', $this->profile->id)
            ->assertHasNoErrors();

        $this->assertFalse((bool)$this->profile->fresh()->is_discoverable);
    }

    public function test_super_admin_can_moderate_and_suspend_reported_portfolios(): void
    {
        $report = PortfolioReport::create([
            'profile_id' => $this->profile->id,
            'reporter_ip' => '192.168.1.1',
            'reason' => 'spam',
            'details' => 'Phishing link detected in bio section.',
            'status' => 'pending',
        ]);

        Volt::actingAs($this->superAdmin)
            ->test('super-admin.reports')
            ->call('suspendAndUnpublish', $report->id)
            ->assertHasNoErrors();

        $this->assertEquals('resolved', $report->fresh()->status);
        $this->assertFalse((bool)$this->profile->fresh()->is_published);
    }

    public function test_super_admin_can_trigger_system_cache_purge(): void
    {
        Volt::actingAs($this->superAdmin)
            ->test('super-admin.system')
            ->call('purgeOptimize')
            ->assertHasNoErrors()
            ->assertSee('successfully purged');
    }
}

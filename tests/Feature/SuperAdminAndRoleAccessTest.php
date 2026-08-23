<?php

namespace Tests\Feature;

use App\Filament\Pages\AgencyBrandingSettings;
use App\Filament\Pages\BillingSettings;
use App\Filament\Pages\DomainSettings;
use App\Filament\Pages\TeamSettings;
use App\Models\Account;
use App\Models\PortfolioReport;
use App\Models\Profile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
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

        $response = $this->actingAs($regularUser)->get('/super-admin');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_visitor_is_redirected_to_login_from_super_admin(): void
    {
        $response = $this->get('/super-admin');

        $response->assertStatus(302);
        $response->assertRedirect(route('super-admin.login'));
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

        Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $superAdmin->id,
            'slug' => 'commander',
            'is_published' => true,
        ]);

        // Request 1: User Dashboard
        $dashboardResponse = $this->actingAs($superAdmin)->get('/developer/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('TIER 1 / BUILDER ACCESS');

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

    public function test_editor_and_viewer_roles_are_denied_access_to_owner_pages(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $viewer = User::factory()->create();

        $account = Account::factory()->create([
            'owner_user_id' => $owner->id,
            'plan_slug' => 'agency',
        ]);

        $account->members()->attach($editor->id, ['role' => 'editor']);
        $account->members()->attach($viewer->id, ['role' => 'viewer']);

        // Owner has access
        $this->actingAs($owner);
        Filament::setTenant($account);
        $this->assertTrue(BillingSettings::canAccess());
        $this->assertTrue(TeamSettings::canAccess());
        $this->assertTrue(DomainSettings::canAccess());
        $this->assertTrue(AgencyBrandingSettings::canAccess());

        // Editor is blocked
        $this->actingAs($editor);
        Filament::setTenant($account);
        $this->assertFalse(BillingSettings::canAccess());
        $this->assertFalse(TeamSettings::canAccess());
        $this->assertFalse(DomainSettings::canAccess());
        $this->assertFalse(AgencyBrandingSettings::canAccess());

        // Viewer is blocked
        $this->actingAs($viewer);
        Filament::setTenant($account);
        $this->assertFalse(BillingSettings::canAccess());
        $this->assertFalse(TeamSettings::canAccess());
        $this->assertFalse(DomainSettings::canAccess());
        $this->assertFalse(AgencyBrandingSettings::canAccess());
    }

    public function test_volt_super_admin_livewire_actions_work_correctly(): void
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $targetUser = User::factory()->create(['is_super_admin' => false]);
        $account = Account::factory()->create(['owner_user_id' => $superAdmin->id]);

        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $targetUser->id,
            'slug' => 'flagged-site-2',
        ]);

        $report = PortfolioReport::create([
            'profile_id' => $profile->id,
            'reporter_ip' => '127.0.0.1',
            'reason' => 'inappropriate',
            'details' => 'Offensive material',
            'status' => 'pending',
        ]);

        $this->actingAs($superAdmin);

        // Test Promote target user to Super Admin
        Volt::test('super-admin.index')
            ->call('toggleSuperAdmin', $targetUser->id)
            ->assertSee("Successfully updated Super Admin privileges for {$targetUser->name}");

        $this->assertTrue((bool) $targetUser->fresh()->is_super_admin);

        // Test Demote target user back
        Volt::test('super-admin.index')
            ->call('toggleSuperAdmin', $targetUser->id)
            ->assertSee("Successfully updated Super Admin privileges for {$targetUser->name}");

        $this->assertFalse((bool) $targetUser->fresh()->is_super_admin);

        // Test Self-Demote Protection
        Volt::test('super-admin.index')
            ->call('toggleSuperAdmin', $superAdmin->id)
            ->assertSee('Security restriction: You cannot demote your own Super Admin root account');

        $this->assertTrue((bool) $superAdmin->fresh()->is_super_admin);

        // Test Resolve Report action
        Volt::test('super-admin.index')
            ->call('resolveReport', $report->id, 'resolved')
            ->assertSee("Report #{$report->id} status updated to resolved");

        $this->assertEquals('resolved', $report->fresh()->status);
    }
}

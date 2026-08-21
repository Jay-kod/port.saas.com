<?php

namespace Tests\Feature;

use App\Filament\Pages\AgencyBrandingSettings;
use App\Filament\Pages\BillingSettings;
use App\Filament\Pages\TeamSettings;
use App\Filament\Resources\Profiles\Pages\CreateProfile;
use App\Models\Account;
use App\Models\Profile;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AgencyAndTeamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['saas.mode' => true]);

        $router = app('router');
        require base_path('routes/web.php');
        $router->getRoutes()->refreshNameLookups();

        $this->seed(ThemeSeeder::class);
    }

    public function test_team_member_invitation_and_role_management(): void
    {
        $owner = User::factory()->create(['name' => 'Agency Owner', 'email' => 'owner@agency.com']);
        $account = Account::factory()->create([
            'owner_user_id' => $owner->id,
            'plan_slug' => 'agency',
        ]);
        Profile::factory()->create(['account_id' => $account->id, 'user_id' => $owner->id]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($owner);
        Filament::auth()->login($owner);
        Filament::setTenant($account);

        Livewire::test(TeamSettings::class)
            ->assertSuccessful()
            ->set('inviteName', 'Editor Dave')
            ->set('inviteEmail', 'dave@agency.com')
            ->set('inviteRole', 'editor')
            ->call('inviteMember')
            ->assertNotified();

        $dave = User::where('email', 'dave@agency.com')->first();
        $this->assertNotNull($dave);
        $this->assertTrue($account->members()->where('users.id', $dave->id)->exists());
        $this->assertEquals('editor', $account->getUserRole($dave));
        $this->assertFalse($account->canManageBilling($dave));
        $this->assertTrue($account->canManageBilling($owner));
    }

    public function test_billing_settings_restricted_to_owners_only(): void
    {
        $owner = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $owner->id, 'plan_slug' => 'agency']);
        Profile::factory()->create(['account_id' => $account->id, 'user_id' => $owner->id]);

        $editor = User::factory()->create(['name' => 'Editor User']);
        $account->members()->attach($editor->id, ['role' => 'editor']);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        // Owner can access billing
        $this->actingAs($owner);
        Filament::auth()->login($owner);
        Filament::setTenant($account);
        $this->assertTrue(BillingSettings::canAccess());

        // Editor cannot access billing
        $this->actingAs($editor);
        Filament::auth()->login($editor);
        Filament::setTenant($account);
        $this->assertFalse(BillingSettings::canAccess());
    }

    public function test_profile_creation_limits_enforced_by_plan(): void
    {
        // Pro account (max_profiles = 1)
        $proUser = User::factory()->create();
        $proAccount = Account::factory()->create(['owner_user_id' => $proUser->id, 'plan_slug' => 'pro']);
        Profile::factory()->create(['account_id' => $proAccount->id, 'user_id' => $proUser->id]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($proUser);
        Filament::auth()->login($proUser);
        Filament::setTenant($proAccount);

        Livewire::test(CreateProfile::class)
            ->fillForm(['full_name' => 'Second Pro Profile'])
            ->call('create');

        $this->assertEquals(1, $proAccount->profiles()->count());

        // Agency account (max_profiles = 10)
        $agencyUser = User::factory()->create();
        $agencyAccount = Account::factory()->create(['owner_user_id' => $agencyUser->id, 'plan_slug' => 'agency']);
        Profile::factory()->create(['account_id' => $agencyAccount->id, 'user_id' => $agencyUser->id]);

        $this->actingAs($agencyUser);
        Filament::auth()->login($agencyUser);
        Filament::setTenant($agencyAccount);

        Livewire::test(CreateProfile::class)
            ->fillForm([
                'full_name' => 'Client Student Profile',
                'slug' => 'client-student-profile',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertEquals(2, $agencyAccount->profiles()->count());
    }

    public function test_active_profile_session_switching(): void
    {
        $agencyUser = User::factory()->create();
        $agencyAccount = Account::factory()->create(['owner_user_id' => $agencyUser->id, 'plan_slug' => 'agency']);
        $profile1 = Profile::factory()->create(['account_id' => $agencyAccount->id, 'user_id' => $agencyUser->id, 'full_name' => 'Client One']);
        $profile2 = Profile::factory()->create(['account_id' => $agencyAccount->id, 'user_id' => $agencyUser->id, 'full_name' => 'Client Two']);

        Project::create(['profile_id' => $profile1->id, 'title' => 'Project For Client 1', 'slug' => 'proj-1', 'sort_order' => 1]);
        Project::create(['profile_id' => $profile2->id, 'title' => 'Project For Client 2', 'slug' => 'proj-2', 'sort_order' => 1]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($agencyUser);
        Filament::auth()->login($agencyUser);
        Filament::setTenant($agencyAccount);

        // Default resolves profile 1
        $resolved = \App\Filament\Resources\Projects\ProjectResource::resolveCurrentTenantProfile();
        $this->assertEquals($profile1->id, $resolved->id);

        // Switch active profile in session
        session(['active_profile_id' => $profile2->id]);
        $resolvedSwitched = \App\Filament\Resources\Projects\ProjectResource::resolveCurrentTenantProfile();
        $this->assertEquals($profile2->id, $resolvedSwitched->id);
    }

    public function test_white_label_branding_and_public_badge_suppression(): void
    {
        $agencyUser = User::factory()->create();
        $agencyAccount = Account::factory()->create(['owner_user_id' => $agencyUser->id, 'plan_slug' => 'agency']);
        $profile = Profile::factory()->create([
            'account_id' => $agencyAccount->id,
            'user_id' => $agencyUser->id,
            'slug' => 'white-label-client',
            'is_published' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($agencyUser);
        Filament::auth()->login($agencyUser);
        Filament::setTenant($agencyAccount);

        // Enable white label
        Livewire::test(AgencyBrandingSettings::class)
            ->assertSuccessful()
            ->set('custom_brand_name', 'DevBoost Academy')
            ->set('hide_platform_branding', true)
            ->call('saveBranding')
            ->assertNotified();

        $this->assertTrue($agencyAccount->fresh()->hide_platform_branding);

        // Public page should NOT contain "Powered by DevFolio"
        $this->get('/white-label-client')
            ->assertStatus(200)
            ->assertDontSee('Powered by');

        // Toggle branding back on
        $agencyAccount->update(['hide_platform_branding' => false]);
        $this->get('/white-label-client')
            ->assertStatus(200)
            ->assertSee('Powered by');
    }
}

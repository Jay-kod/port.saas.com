<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Domain;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AgencyDashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Account $agencyAccount;
    protected Profile $clientA;
    protected Profile $clientB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'email' => 'agency@example.com',
            'name' => 'Agency Director',
        ]);

        $this->agencyAccount = Account::factory()->create([
            'name' => 'Apex Talent Studio',
            'owner_user_id' => $this->owner->id,
            'plan_slug' => 'agency',
            'ai_generations_used_current_period' => 5,
            'custom_brand_name' => 'Apex Studio',
            'hide_platform_branding' => true,
        ]);

        $this->clientA = Profile::create([
            'account_id' => $this->agencyAccount->id,
            'user_id' => $this->owner->id,
            'slug' => 'sarah-jenkins',
            'full_name' => 'Sarah Jenkins',
            'headline' => 'Staff Cloud Architect',
            'is_published' => true,
            'theme_mode_default' => 'dark',
        ]);

        $this->clientB = Profile::create([
            'account_id' => $this->agencyAccount->id,
            'user_id' => $this->owner->id,
            'slug' => 'david-vance',
            'full_name' => 'David Vance',
            'headline' => 'Full-Stack Developer',
            'is_published' => false,
            'theme_mode_default' => 'dark',
        ]);
    }

    public function test_guest_is_redirected_to_agency_login_for_all_agency_pages(): void
    {
        $routes = [
            'agency',
            'agency.clients',
            'agency.team',
            'agency.branding',
            'agency.domains',
            'agency.billing',
            'agency.analytics',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertRedirect('/agency/login');
        }
    }

    public function test_authenticated_agency_owner_can_access_all_7_agency_pages_with_http_200(): void
    {
        $routes = [
            'agency' => 'Agency Client Command Center',
            'agency.clients' => 'Client Portfolios Manager',
            'agency.team' => 'Team & Seats Manager',
            'agency.branding' => 'White-Label & Agency Branding',
            'agency.domains' => 'Client Custom Domains',
            'agency.billing' => 'Agency Billing & Multi-Client Quotas',
            'agency.analytics' => 'Agency Operations & Analytics Center',
        ];

        foreach ($routes as $routeName => $needle) {
            $response = $this->actingAs($this->owner)->get(route($routeName));
            $response->assertOk();
            $response->assertSee($needle, false);
        }
    }

    public function test_agency_can_provision_and_switch_active_client_profile(): void
    {
        Volt::actingAs($this->owner)
            ->test('agency.clients')
            ->set('fullName', 'Michael Chang')
            ->set('headline', 'Principal AI Researcher')
            ->set('slug', 'michael-chang')
            ->set('themeId', 1)
            ->set('isPublished', true)
            ->call('saveClient')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('profiles', [
            'account_id' => $this->agencyAccount->id,
            'full_name' => 'Michael Chang',
            'slug' => 'michael-chang',
            'is_published' => true,
        ]);

        $created = Profile::where('slug', 'michael-chang')->first();
        $this->assertNotNull($created);

        // Test active client context switching
        Volt::actingAs($this->owner)
            ->test('agency.clients')
            ->call('switchProfile', $created->id);

        $this->assertEquals($created->id, session('active_profile_id'));
    }

    public function test_agency_can_invite_team_members_with_roles(): void
    {
        Volt::actingAs($this->owner)
            ->test('agency.team')
            ->set('inviteEmail', 'designer@apexstudio.com')
            ->set('inviteRole', 'editor')
            ->call('inviteMember')
            ->assertHasNoErrors();

        $invitedUser = User::where('email', 'designer@apexstudio.com')->first();
        $this->assertNotNull($invitedUser);

        $this->assertDatabaseHas('account_user', [
            'account_id' => $this->agencyAccount->id,
            'user_id' => $invitedUser->id,
            'role' => 'editor',
        ]);
    }

    public function test_agency_can_save_white_label_branding(): void
    {
        Volt::actingAs($this->owner)
            ->test('agency.branding')
            ->set('customBrandName', 'Apex Engineering Group')
            ->set('customLogoPath', 'https://apex.com/logo.png')
            ->set('hidePlatformBranding', true)
            ->call('saveBranding')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('accounts', [
            'id' => $this->agencyAccount->id,
            'custom_brand_name' => 'Apex Engineering Group',
            'custom_logo_path' => 'https://apex.com/logo.png',
            'hide_platform_branding' => true,
        ]);
    }

    public function test_agency_can_connect_and_verify_client_domains(): void
    {
        Volt::actingAs($this->owner)
            ->test('agency.domains')
            ->set('selectedProfileId', $this->clientA->id)
            ->set('newDomain', 'sarahjenkins.dev')
            ->call('addDomain')
            ->assertHasNoErrors();

        $domain = Domain::where('domain', 'sarahjenkins.dev')->first();
        $this->assertNotNull($domain);
        $this->assertEquals($this->clientA->id, $domain->profile_id);

        // Verify domain
        Volt::actingAs($this->owner)
            ->test('agency.domains')
            ->call('verifyDomain', $domain->id);

        $this->assertNotNull($domain->fresh()->verified_at);
    }

    public function test_agency_analytics_computes_multi_client_aggregates(): void
    {
        Project::create([
            'profile_id' => $this->clientA->id,
            'title' => 'Global Payments Engine',
            'slug' => 'global-payments',
            'tech_stack' => ['Laravel', 'PostgreSQL', 'Stripe'],
            'is_featured' => true,
        ]);

        Skill::create([
            'profile_id' => $this->clientA->id,
            'name' => 'PostgreSQL',
            'category' => 'Database',
            'proficiency' => 95,
        ]);

        Volt::actingAs($this->owner)
            ->test('agency.analytics')
            ->assertSee('Agency Operations', false)
            ->assertSee('Client Portfolio Health Leaderboard', false)
            ->assertSee('Sarah Jenkins', false)
            ->assertSee('David Vance', false)
            ->assertSee('PostgreSQL', false);
    }
}

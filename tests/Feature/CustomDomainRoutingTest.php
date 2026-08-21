<?php

namespace Tests\Feature;

use App\Filament\Pages\DomainSettings;
use App\Models\Account;
use App\Models\Domain;
use App\Models\Profile;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomDomainRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['saas.mode' => true]);

        // Re-evaluate routes under SAAS_MODE=true without wiping Filament panel routes
        $router = app('router');
        require base_path('routes/web.php');
        $router->getRoutes()->refreshNameLookups();

        $this->seed(ThemeSeeder::class);
    }

    public function test_verified_custom_domain_serves_tenant_content_at_root_and_subroutes(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'plan_slug' => 'pro',
        ]);

        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'sarah-dev',
            'full_name' => 'Sarah Developer',
            'is_published' => true,
        ]);

        Project::create([
            'profile_id' => $profile->id,
            'title' => 'Kubernetes Auto-Scaler',
            'slug' => 'k8s-scaler',
            'description' => 'Automated cloud infrastructure scaler',
            'sort_order' => 1,
            'is_featured' => true,
        ]);

        Domain::create([
            'profile_id' => $profile->id,
            'domain' => 'resume.sarah.dev',
            'verified_at' => now(),
        ]);

        // Request to root / on custom domain
        $response = $this->get('http://resume.sarah.dev/');

        $response->assertStatus(200)
            ->assertSee('Sarah Developer');

        // Request to /projects on custom domain
        $projResponse = $this->get('http://resume.sarah.dev/projects');

        $projResponse->assertStatus(200)
            ->assertSee('Kubernetes Auto-Scaler');
    }

    public function test_unverified_custom_domain_does_not_serve_tenant_content(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'plan_slug' => 'pro',
        ]);

        Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'alex-engineer',
            'full_name' => 'Alex Engineer',
            'is_published' => true,
        ]);

        // Unverified domain (verified_at = null)
        Domain::create([
            'profile_id' => $account->profiles->first()->id,
            'domain' => 'pending.alex.dev',
            'verified_at' => null,
        ]);

        // Subpage should 404 because domain is not verified
        $response = $this->get('http://pending.alex.dev/about');

        $response->assertStatus(404);
    }

    public function test_custom_domain_tenant_isolation(): void
    {
        $userA = User::factory()->create();
        $accountA = Account::factory()->create(['owner_user_id' => $userA->id, 'plan_slug' => 'pro']);
        $profileA = Profile::factory()->create([
            'account_id' => $accountA->id,
            'user_id' => $userA->id,
            'slug' => 'tenant-a',
            'full_name' => 'Tenant Alice',
            'is_published' => true,
        ]);
        Domain::create([
            'profile_id' => $profileA->id,
            'domain' => 'alice.portfolio.dev',
            'verified_at' => now(),
        ]);
        Project::create([
            'profile_id' => $profileA->id,
            'title' => 'Alice Secret Project',
            'slug' => 'alice-project',
            'sort_order' => 1,
            'is_featured' => true,
        ]);

        $userB = User::factory()->create();
        $accountB = Account::factory()->create(['owner_user_id' => $userB->id, 'plan_slug' => 'pro']);
        $profileB = Profile::factory()->create([
            'account_id' => $accountB->id,
            'user_id' => $userB->id,
            'slug' => 'tenant-b',
            'full_name' => 'Tenant Bob',
            'is_published' => true,
        ]);
        Project::create([
            'profile_id' => $profileB->id,
            'title' => 'Bob Private System',
            'slug' => 'bob-project',
            'sort_order' => 1,
            'is_featured' => true,
        ]);

        // Access Alice's domain -> Alice's project visible, Bob's project NEVER visible
        $response = $this->get('http://alice.portfolio.dev/projects');

        $response->assertStatus(200)
            ->assertSee('Alice Secret Project')
            ->assertDontSee('Bob Private System');
    }

    public function test_plan_gating_restricts_free_plan_and_allows_pro_plan(): void
    {
        // Free account
        $freeUser = User::factory()->create();
        $freeAccount = Account::factory()->create(['owner_user_id' => $freeUser->id, 'plan_slug' => 'free']);
        Profile::factory()->create(['account_id' => $freeAccount->id, 'user_id' => $freeUser->id]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($freeUser);
        Filament::auth()->login($freeUser);
        Filament::setTenant($freeAccount);

        Livewire::test(DomainSettings::class)
            ->assertSuccessful()
            ->assertSee('Custom Domains are a Pro Feature')
            ->set('newDomain', 'test.freedomain.com')
            ->call('addDomain')
            ->assertNotified();

        $this->assertDatabaseMissing('domains', ['domain' => 'test.freedomain.com']);

        // Pro account
        $proUser = User::factory()->create();
        $proAccount = Account::factory()->create(['owner_user_id' => $proUser->id, 'plan_slug' => 'pro']);
        $proProfile = Profile::factory()->create(['account_id' => $proAccount->id, 'user_id' => $proUser->id]);

        $this->actingAs($proUser);
        Filament::auth()->login($proUser);
        Filament::setTenant($proAccount);

        Livewire::test(DomainSettings::class)
            ->assertSuccessful()
            ->assertDontSee('Custom Domains are a Pro Feature')
            ->set('newDomain', 'portfolio.pro-dev.com')
            ->call('addDomain')
            ->assertNotified();

        $this->assertDatabaseHas('domains', [
            'profile_id' => $proProfile->id,
            'domain' => 'portfolio.pro-dev.com',
        ]);
    }

    public function test_domain_verification_and_removal_via_filament_page(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id, 'plan_slug' => 'pro']);
        $profile = Profile::factory()->create(['account_id' => $account->id, 'user_id' => $user->id]);

        $domain = Domain::create([
            'profile_id' => $profile->id,
            'domain' => 'https://My-Site.Example.com/',
            'verified_at' => null,
        ]);

        // Assert domain is normalized on creation
        $this->assertEquals('my-site.example.com', $domain->domain);
        $this->assertNotNull($domain->verification_token);
        $this->assertFalse($domain->isVerified());

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
        Filament::auth()->login($user);
        Filament::setTenant($account);

        // Verify domain
        Livewire::test(DomainSettings::class)
            ->assertSuccessful()
            ->call('verifyDomain', $domain->id)
            ->assertNotified();

        $this->assertTrue($domain->fresh()->isVerified());

        // Remove domain
        Livewire::test(DomainSettings::class)
            ->assertSuccessful()
            ->call('removeDomain', $domain->id)
            ->assertNotified();

        $this->assertDatabaseMissing('domains', ['id' => $domain->id]);
    }

    public function test_cannot_add_duplicate_domain_already_claimed(): void
    {
        $userA = User::factory()->create();
        $accountA = Account::factory()->create(['owner_user_id' => $userA->id, 'plan_slug' => 'pro']);
        $profileA = Profile::factory()->create(['account_id' => $accountA->id, 'user_id' => $userA->id]);
        Domain::create([
            'profile_id' => $profileA->id,
            'domain' => 'claimed.example.com',
            'verified_at' => now(),
        ]);

        $userB = User::factory()->create();
        $accountB = Account::factory()->create(['owner_user_id' => $userB->id, 'plan_slug' => 'pro']);
        Profile::factory()->create(['account_id' => $accountB->id, 'user_id' => $userB->id]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($userB);
        Filament::auth()->login($userB);
        Filament::setTenant($accountB);

        Livewire::test(DomainSettings::class)
            ->assertSuccessful()
            ->set('newDomain', 'claimed.example.com')
            ->call('addDomain')
            ->assertNotified();

        // Ensure Domain remains owned solely by Profile A
        $this->assertEquals(1, Domain::where('domain', 'claimed.example.com')->count());
        $this->assertEquals($profileA->id, Domain::where('domain', 'claimed.example.com')->first()->profile_id);
    }
}

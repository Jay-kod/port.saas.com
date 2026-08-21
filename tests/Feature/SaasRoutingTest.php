<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Certificate;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 3 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * Tests SaaS multi-tenant routing (SAAS_MODE=true), including the marketing
 * homepage, pricing page, tenant slug resolution, and HTTP-level data isolation.
 */
class SaasRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ThemeSeeder::class);
        config(['saas.mode' => true]);

        $router = app('router');
        $router->setRoutes(new \Illuminate\Routing\RouteCollection());
        require base_path('routes/web.php');
        $router->getRoutes()->refreshNameLookups();
    }

    public function test_marketing_home_and_pricing_render_in_saas_mode(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('DevFolio')
            ->assertSee('Turn your code into an');

        $this->get('/pricing')
            ->assertStatus(200)
            ->assertSee('Pro Developer')
            ->assertSee('Agency / Team');
    }

    public function test_saas_mode_routes_tenant_portfolios_by_slug_with_isolation(): void
    {
        // --- Tenant A ---
        $ownerA = User::factory()->create();
        $accountA = Account::factory()->create(['owner_user_id' => $ownerA->id]);
        $profileA = Profile::factory()->create([
            'account_id' => $accountA->id,
            'user_id' => $ownerA->id,
            'slug' => 'alice-coder',
            'full_name' => 'Alice Coder',
            'is_published' => true,
        ]);
        $projectA = Project::factory()->create([
            'profile_id' => $profileA->id,
            'title' => 'Alice Neural Engine',
            'slug' => 'alice-neural-engine',
        ]);
        $skillA = Skill::create([
            'profile_id' => $profileA->id,
            'name' => 'Quantum Computing',
            'category' => 'Advanced',
            'proficiency' => 95,
        ]);
        $certA = Certificate::create([
            'profile_id' => $profileA->id,
            'title' => 'Alice Cloud Architect',
            'slug' => 'alice-cloud-architect',
            'issuer' => 'Cloud Academy',
        ]);

        // --- Tenant B ---
        $ownerB = User::factory()->create();
        $accountB = Account::factory()->create(['owner_user_id' => $ownerB->id]);
        $profileB = Profile::factory()->create([
            'account_id' => $accountB->id,
            'user_id' => $ownerB->id,
            'slug' => 'bob-builder',
            'full_name' => 'Bob Builder',
            'is_published' => true,
        ]);
        $projectB = Project::factory()->create([
            'profile_id' => $profileB->id,
            'title' => 'Bob Microservice Mesh',
            'slug' => 'bob-microservice-mesh',
        ]);

        // Tenant A Root & Subpages
        $this->get('/alice-coder')
            ->assertStatus(200)
            ->assertSee('Alice Coder');

        $this->get('/alice-coder/projects')
            ->assertStatus(200)
            ->assertSee('Alice Neural Engine')
            ->assertDontSee('Bob Microservice Mesh');

        $this->get('/alice-coder/projects/alice-neural-engine')
            ->assertStatus(200)
            ->assertSee('Alice Neural Engine');

        $this->get('/alice-coder/skills')
            ->assertStatus(200)
            ->assertSee('Quantum Computing');

        $this->get('/alice-coder/certificates')
            ->assertStatus(200)
            ->assertSee('Alice Cloud Architect');

        $this->get('/alice-coder/certificates/alice-cloud-architect')
            ->assertStatus(200)
            ->assertSee('Alice Cloud Architect');

        $this->get('/alice-coder/contact')
            ->assertStatus(200)
            ->assertSee($profileA->email);

        // Tenant B Projects
        $this->get('/bob-builder/projects')
            ->assertStatus(200)
            ->assertSee('Bob Microservice Mesh')
            ->assertDontSee('Alice Neural Engine');
    }

    public function test_unpublished_profile_returns_404_in_saas_mode(): void
    {
        $owner = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $owner->id]);
        Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $owner->id,
            'slug' => 'hidden-dev',
            'is_published' => false,
        ]);

        $this->get('/hidden-dev')->assertStatus(404);
        $this->get('/hidden-dev/projects')->assertStatus(404);
    }

    public function test_non_existent_slug_returns_404_in_saas_mode(): void
    {
        $this->get('/non-existent-user')->assertStatus(404);
        $this->get('/non-existent-user/projects')->assertStatus(404);
    }
}

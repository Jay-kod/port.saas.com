<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Certificate;
use App\Models\Profile;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the single-tenant / self-hosted public routes (SAAS_MODE=false).
 *
 * SaaS NOTE (Phase 0/3): this test is the baseline referenced by
 * docs/agents/01-GROUNDWORK.md's acceptance criteria — "every public
 * route still returns 200 and renders the same content as before"
 * after introducing CurrentProfileResolver. Extend it, don't replace
 * it, when Phase 3 adds SAAS_MODE=true routing.
 *
 * Phase 1: updated to create an Account/User before Profile, since
 * account_id, user_id, and slug are now NOT NULL.
 */
class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['saas.mode' => false]);

        $router = app('router');
        $router->setRoutes(new \Illuminate\Routing\RouteCollection());
        require base_path('routes/web.php');
        $router->getRoutes()->refreshNameLookups();

        $this->seed(ThemeSeeder::class);
    }

    public function test_home_page_renders_without_a_profile(): void
    {
        $this->get(route('home'))->assertStatus(200);
    }

    public function test_all_public_pages_render_with_a_profile(): void
    {
        $owner = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $owner->id]);

        $profile = Profile::create([
            'account_id' => $account->id,
            'user_id' => $owner->id,
            'slug' => 'jane-tester',
            'full_name' => 'Jane Tester',
            'headline' => 'QA Engineer',
            'bio' => 'Testing things.',
            'email' => 'jane@example.com',
        ]);

        $project = Project::create([
            'profile_id' => $profile->id,
            'title' => 'Test Project',
            'slug' => 'test-project',
            'summary' => 'A project used in tests.',
        ]);

        $certificate = Certificate::create([
            'profile_id' => $profile->id,
            'title' => 'Test Certificate',
            'slug' => 'test-certificate',
            'issuer' => 'Testing Co',
        ]);

        $this->get(route('home'))->assertStatus(200)->assertSee($profile->full_name);
        $this->get(route('about'))->assertStatus(200);
        $this->get(route('projects'))->assertStatus(200)->assertSee($project->title);
        $this->get(route('projects.show', $project->slug))->assertStatus(200)->assertSee($project->title);
        $this->get(route('skills'))->assertStatus(200);
        $this->get(route('certificates'))->assertStatus(200)->assertSee($certificate->title);
        $this->get(route('certificates.show', $certificate->slug))->assertStatus(200)->assertSee($certificate->title);
        $this->get(route('contact'))->assertStatus(200)->assertSee($profile->email);
    }

    public function test_unknown_project_slug_404s(): void
    {
        $this->get(route('projects.show', 'does-not-exist'))->assertStatus(404);
    }
}

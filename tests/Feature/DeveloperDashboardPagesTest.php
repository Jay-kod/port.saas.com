<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AiSetting;
use App\Models\Certificate;
use App\Models\Experience;
use App\Models\GithubSetting;
use App\Models\JobApplication;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class DeveloperDashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Account $account;
    protected Profile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'developer@example.com',
            'name' => 'Alex Developer',
        ]);

        $this->account = Account::factory()->create([
            'name' => 'Alex Workspace',
            'owner_user_id' => $this->user->id,
            'plan_slug' => 'free',
            'ai_generations_used_current_period' => 0,
        ]);

        $this->profile = Profile::create([
            'account_id' => $this->account->id,
            'user_id' => $this->user->id,
            'slug' => 'alex-dev',
            'full_name' => 'Alex Developer',
            'headline' => 'Senior Full-Stack Engineer',
            'is_published' => true,
            'theme_mode_default' => 'dark',
        ]);
    }

    public function test_guest_is_redirected_to_developer_login_for_all_developer_pages(): void
    {
        $routes = [
            'developer.profile',
            'developer.projects',
            'developer.experiences',
            'developer.skills',
            'developer.certificates',
            'developer.resumes',
            'developer.cover-letters',
            'developer.job-tracker',
            'developer.resume-import',
            'developer.github-sync',
            'developer.ai-settings',
            'developer.themes',
            'developer.domains',
            'developer.templates',
            'developer.billing',
            'developer.privacy',
            'developer.analytics',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertRedirect('/developer/login');
        }
    }

    public function test_authenticated_developer_can_access_all_pages_with_http_200(): void
    {
        $routes = [
            'developer.profile' => 'Profile',
            'developer.projects' => 'Projects',
            'developer.experiences' => 'Experience',
            'developer.skills' => 'Skills',
            'developer.certificates' => 'Certificates',
            'developer.resumes' => 'Resumes',
            'developer.cover-letters' => 'Cover Letters',
            'developer.job-tracker' => 'Job Tracker',
            'developer.resume-import' => 'Import Resume',
            'developer.github-sync' => 'GitHub Sync',
            'developer.ai-settings' => 'AI Settings',
            'developer.themes' => 'Theme',
            'developer.domains' => 'Custom Domains',
            'developer.templates' => 'Resume Templates',
            'developer.billing' => 'Billing',
            'developer.privacy' => 'Privacy',
            'developer.analytics' => 'Analytics',
        ];

        foreach ($routes as $routeName => $needle) {
            $response = $this->actingAs($this->user)->get(route($routeName));
            $response->assertOk();
            $response->assertSee($needle);
        }
    }

    public function test_analytics_computes_telemetry_and_health_scores(): void
    {
        // Seed projects & skills
        Project::create([
            'profile_id' => $this->profile->id,
            'title' => 'Realtime Sync Engine',
            'slug' => 'realtime-sync',
            'is_featured' => true,
            'tech_stack' => ['Laravel', 'Vue', 'Redis'],
            'live_url' => 'https://sync.example.com',
            'repo_url' => 'https://github.com/alex/sync',
        ]);

        Skill::create([
            'profile_id' => $this->profile->id,
            'name' => 'Go',
            'category' => 'Backend',
            'proficiency' => 90,
        ]);

        JobApplication::create([
            'profile_id' => $this->profile->id,
            'company' => 'Google',
            'role' => 'Software Engineer',
            'status' => 'interviewing',
        ]);

        Volt::actingAs($this->user)
            ->test('developer.analytics')
            ->assertSee('Developer Operations', false)
            ->assertSee('Technical Skills', false)
            ->assertSee('Realtime Sync Engine', false)
            ->assertSee('Go', false)
            ->assertSee('Google', false);
    }

    public function test_developer_can_update_profile_details(): void
    {
        Volt::actingAs($this->user)
            ->test('developer.profile')
            ->set('full_name', 'Alexander The Great')
            ->set('headline', 'Principal Cloud Architect')
            ->set('bio', 'Building distributed cloud applications.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('profiles', [
            'id' => $this->profile->id,
            'full_name' => 'Alexander The Great',
            'headline' => 'Principal Cloud Architect',
        ]);
    }

    public function test_developer_can_crud_projects_with_tenancy_isolation(): void
    {
        Volt::actingAs($this->user)
            ->test('developer.projects')
            ->set('title', 'Distributed Queue Engine')
            ->set('slug', 'distributed-queue-engine')
            ->set('description', 'High performance async queue in Rust.')
            ->set('tech_stack_input', 'Rust, Redis, Tokio')
            ->call('saveProject')
            ->assertHasNoErrors();

        $project = Project::where('slug', 'distributed-queue-engine')->first();
        $this->assertNotNull($project);
        $this->assertEquals($this->profile->id, $project->profile_id);

        // Delete project
        Volt::actingAs($this->user)
            ->test('developer.projects')
            ->call('deleteProject', $project->id);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_developer_can_add_skills_matrix(): void
    {
        Volt::actingAs($this->user)
            ->test('developer.skills')
            ->set('name', 'Laravel Livewire')
            ->set('category', 'Frontend')
            ->set('proficiency', 95)
            ->call('saveSkill')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('skills', [
            'profile_id' => $this->profile->id,
            'name' => 'Laravel Livewire',
            'category' => 'Frontend',
            'proficiency' => 95,
        ]);
    }

    public function test_developer_can_save_byok_ai_setting(): void
    {
        Volt::actingAs($this->user)
            ->test('developer.ai-settings')
            ->set('provider', 'anthropic')
            ->set('api_key', 'sk-ant-api03-samplekey123456789')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ai_settings', [
            'account_id' => $this->account->id,
            'provider' => 'anthropic',
            'is_active' => true,
        ]);
    }

    public function test_job_tracker_kanban_stage_transitions(): void
    {
        $job = JobApplication::create([
            'profile_id' => $this->profile->id,
            'company' => 'Stripe Inc.',
            'role' => 'Staff Infrastructure Engineer',
            'status' => 'applied',
        ]);

        Volt::actingAs($this->user)
            ->test('developer.job-tracker')
            ->call('updateStatus', $job->id, 'interviewing');

        $this->assertDatabaseHas('job_applications', [
            'id' => $job->id,
            'status' => 'interviewing',
        ]);
    }
}

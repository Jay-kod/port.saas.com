<?php

namespace Tests\Feature;

use App\Filament\Pages\AgencyBrandingSettings;
use App\Filament\Pages\DomainSettings;
use App\Filament\Pages\JobTracker;
use App\Filament\Pages\PrivacyAndData;
use App\Filament\Pages\ResumeImport;
use App\Filament\Pages\ThemeSelector;
use App\Filament\Resources\CoverLetterGenerations\Pages\CreateCoverLetterGeneration;
use App\Filament\Resources\Profiles\Pages\CreateProfile;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\ResumeGenerations\Pages\CreateResumeGeneration;
use App\Models\Account;
use App\Models\AiSetting;
use App\Models\CoverLetterGeneration;
use App\Models\Domain;
use App\Models\Experience;
use App\Models\JobApplication;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ResumeGeneration;
use App\Models\Skill;
use App\Models\Theme;
use App\Models\User;
use App\Services\AiUsageGuard;
use App\Services\GdprService;
use App\Services\ResumeParserService;
use Database\Seeders\ThemeSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * 10 Comprehensive End-to-End Real-World Scenario Tests
 * Testing the complete multi-tenant SaaS platform lifecycles from start to finish.
 */
class RealWorldE2EScenariosTest extends TestCase
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

    /**
     * Scenario 1: Complete New User Journey (Registration -> Onboarding -> Live Portfolio)
     */
    public function test_scenario_01_registration_to_onboarding_to_live_portfolio(): void
    {
        // 1. User registers
        $user = User::factory()->create(['name' => 'Sarah Dev', 'email' => 'sarah@example.com']);
        $account = Account::create(['name' => "Sarah's Workspace", 'owner_user_id' => $user->id, 'plan_slug' => 'free']);
        $profile = Profile::create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'full_name' => 'Sarah Dev',
            'slug' => 'sarah-dev',
            'is_published' => false,
        ]);

        $this->actingAs($user);

        // 2. Onboarding wizard
        $theme = Theme::where('slug', 'toxic-cyberpunk')->first();

        Livewire::test('onboarding')
            ->set('slug', 'sarah-dev')
            ->set('full_name', 'Sarah Jenkins')
            ->set('headline', 'Senior Cloud Architect')
            ->set('bio', 'Specializing in high-throughput distributed systems.')
            ->set('location', 'San Francisco, CA')
            ->set('selected_theme_id', $theme->id)
            ->call('saveAndFinish');

        $profile->refresh();
        $this->assertTrue($profile->is_published);
        $this->assertEquals('Sarah Jenkins', $profile->full_name);
        $this->assertEquals($theme->id, $profile->theme_id);

        // 3. Public Portfolio is Live
        $response = $this->get('/sarah-dev');
        $response->assertStatus(200)
            ->assertSee('Sarah Jenkins')
            ->assertSee('Senior Cloud Architect')
            ->assertSee('Specializing in high-throughput distributed systems.');

        $this->get('/sarah-dev/about')
            ->assertStatus(200)
            ->assertSee('About Sarah Jenkins');
    }

    /**
     * Scenario 2: Developer Portfolio Content Management & Public Sync
     */
    public function test_scenario_02_portfolio_content_creation_and_public_sync(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'alex-engineer',
            'is_published' => true,
        ]);

        // Add Project, Skill, and Experience
        Project::create([
            'profile_id' => $profile->id,
            'title' => 'Real-Time Telemetry Engine',
            'slug' => 'real-time-telemetry-engine',
            'description' => 'Processes 50k events per second using Redis and Go.',
            'sort_order' => 1,
            'featured' => true,
        ]);

        Skill::create([
            'profile_id' => $profile->id,
            'name' => 'Distributed Systems',
            'category' => 'Backend',
            'sort_order' => 1,
        ]);

        Experience::create([
            'profile_id' => $profile->id,
            'title' => 'Staff Engineer',
            'company' => 'Stripe',
            'start_date' => '2022-01-01',
            'sort_order' => 1,
        ]);

        // Verify public portfolio pages
        $this->get('/alex-engineer/projects')
            ->assertStatus(200)
            ->assertSee('Real-Time Telemetry Engine');

        $this->get('/alex-engineer/projects/real-time-telemetry-engine')
            ->assertStatus(200)
            ->assertSee('Processes 50k events per second');

        $this->get('/alex-engineer/skills')
            ->assertStatus(200)
            ->assertSee('Distributed Systems');
    }

    /**
     * Scenario 3: AI Resume Import & Parsing Pipeline to Portfolio
     */
    public function test_scenario_03_ai_resume_import_and_portfolio_hydration(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'imported-dev',
            'is_published' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
        Filament::auth()->login($user);
        Filament::setTenant($account);

        $sampleResumeText = <<<TXT
Johnathan Carter
Fullstack Laravel & React Developer | Austin, TX
john@carter.dev

Summary: Over 8 years building scalable SaaS web platforms.

Experience:
Senior Software Architect at ScaleTech (2020 - Present)
Led development of enterprise multi-tenant analytics microservices.

Skills: Laravel, PHP 8, Vue.js, Tailwind CSS, Docker, PostgreSQL

Projects:
CloudPulse - Real-time infrastructure health monitor.
TXT;

        // Step 1: Parse resume text
        $component = Livewire::test(ResumeImport::class)
            ->set('resumeText', $sampleResumeText)
            ->call('parseResume')
            ->assertSet('step', 2);

        // Step 2: Confirm import into profile
        $component->call('importParsedData')
            ->assertNotified();

        $this->assertEquals(1, $profile->experiences()->count());
        $this->assertGreaterThanOrEqual(1, $profile->skills()->count());
        $this->assertEquals(1, $profile->projects()->count());
        $this->assertEquals('Johnathan Carter', $profile->fresh()->full_name);
    }

    /**
     * Scenario 4: Free Tier AI Quota Metering & Monthly Limit Enforcement
     */
    public function test_scenario_04_ai_usage_metering_limits_and_byok_exemption(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id, 'plan_slug' => 'free']);
        $profile = Profile::factory()->create(['account_id' => $account->id, 'user_id' => $user->id]);

        $guard = app(AiUsageGuard::class);

        // Consume 3 free generations
        $guard->recordGeneration($account);
        $guard->recordGeneration($account);
        $guard->recordGeneration($account);

        $this->assertEquals(3, $account->fresh()->ai_generations_used_current_period);
        $this->assertFalse($guard->canGenerate($account));

        // BYOK exemption: adding an active custom API key grants unlimited access
        AiSetting::create([
            'account_id' => $account->id,
            'provider' => 'openai',
            'api_key' => 'sk-custom-test-key',
            'is_active' => true,
        ]);

        $this->assertTrue($guard->canGenerate($account));
    }

    /**
     * Scenario 5: Pro Tier Upgrade & Custom Domain Lifecycle
     */
    public function test_scenario_05_pro_upgrade_and_custom_domain_routing(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id, 'plan_slug' => 'pro']);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'pro-developer',
            'full_name' => 'Elena Rostova',
            'is_published' => true,
        ]);

        Project::create([
            'profile_id' => $profile->id,
            'title' => 'Pro AI Assistant',
            'slug' => 'pro-ai-assistant',
            'sort_order' => 1,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
        Filament::auth()->login($user);
        Filament::setTenant($account);

        // Connect domain
        Livewire::test(DomainSettings::class)
            ->set('newDomain', 'https://ElenaCodes.io/')
            ->call('addDomain')
            ->assertNotified();

        $domain = Domain::where('domain', 'elenacodes.io')->first();
        $this->assertNotNull($domain);
        $this->assertNull($domain->verified_at);

        // Verify domain
        $domain->update(['verified_at' => now()]);

        // HTTP request via custom domain
        $this->get('http://elenacodes.io/')
            ->assertStatus(200)
            ->assertSee('Elena Rostova');

        $this->get('http://elenacodes.io/projects')
            ->assertStatus(200)
            ->assertSee('Pro AI Assistant');
    }

    /**
     * Scenario 6: Agency Multi-Client Portfolio Management & Profile Switching
     */
    public function test_scenario_06_agency_multi_client_profiles_and_switching(): void
    {
        $agencyUser = User::factory()->create();
        $agencyAccount = Account::factory()->create(['owner_user_id' => $agencyUser->id, 'plan_slug' => 'agency']);

        $alexProfile = Profile::factory()->create([
            'account_id' => $agencyAccount->id,
            'user_id' => $agencyUser->id,
            'full_name' => 'Student Alex',
            'slug' => 'student-alex',
            'is_published' => true,
        ]);

        $bethProfile = Profile::factory()->create([
            'account_id' => $agencyAccount->id,
            'user_id' => $agencyUser->id,
            'full_name' => 'Student Beth',
            'slug' => 'student-beth',
            'is_published' => true,
        ]);

        Project::create(['profile_id' => $alexProfile->id, 'title' => 'Alex Rust Compiler', 'slug' => 'alex-rust', 'sort_order' => 1]);
        Project::create(['profile_id' => $bethProfile->id, 'title' => 'Beth Fintech App', 'slug' => 'beth-fintech', 'sort_order' => 1]);

        // Verify independent client routes
        $this->get('/student-alex/projects')
            ->assertStatus(200)
            ->assertSee('Alex Rust Compiler')
            ->assertDontSee('Beth Fintech App');

        $this->get('/student-beth/projects')
            ->assertStatus(200)
            ->assertSee('Beth Fintech App')
            ->assertDontSee('Alex Rust Compiler');

        // Session switching scopes admin resources
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($agencyUser);
        Filament::auth()->login($agencyUser);
        Filament::setTenant($agencyAccount);

        session(['active_profile_id' => $bethProfile->id]);
        $resolved = ProjectResource::resolveCurrentTenantProfile();
        $this->assertNotNull($resolved);
        $this->assertEquals($bethProfile->id, $resolved->id);
    }

    /**
     * Scenario 7: Agency White-Label Branding & Platform Badge Suppression
     */
    public function test_scenario_07_agency_white_label_branding_controls(): void
    {
        $agencyUser = User::factory()->create();
        $agencyAccount = Account::factory()->create(['owner_user_id' => $agencyUser->id, 'plan_slug' => 'agency']);
        $profile = Profile::factory()->create([
            'account_id' => $agencyAccount->id,
            'user_id' => $agencyUser->id,
            'slug' => 'branded-student',
            'is_published' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($agencyUser);
        Filament::auth()->login($agencyUser);
        Filament::setTenant($agencyAccount);

        Livewire::test(AgencyBrandingSettings::class)
            ->set('custom_brand_name', 'TechLaunch Academy')
            ->set('hide_platform_branding', true)
            ->call('saveBranding')
            ->assertNotified();

        $this->get('/branded-student')
            ->assertStatus(200)
            ->assertDontSee('Powered by');
    }

    /**
     * Scenario 8: Tailored Cover Letter & Job Application Kanban Workflow
     */
    public function test_scenario_08_cover_letter_and_job_tracker_kanban_flow(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id, 'plan_slug' => 'pro']);
        $profile = Profile::factory()->create(['account_id' => $account->id, 'user_id' => $user->id]);

        CoverLetterGeneration::create([
            'profile_id' => $profile->id,
            'job_title' => 'Senior Backend Engineer',
            'company_name' => 'GitHub',
            'job_description' => 'Looking for senior engineers with high scale experience.',
            'cover_letter' => 'Dear GitHub Hiring Team, I am thrilled to apply...',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
        Filament::auth()->login($user);
        Filament::setTenant($account);

        // Add application via JobTracker Kanban
        Livewire::test(JobTracker::class)
            ->set('company', 'GitHub')
            ->set('role', 'Senior Backend Engineer')
            ->set('status', 'applied')
            ->set('salary_range', '$180,000')
            ->call('saveApplication')
            ->assertNotified();

        $application = JobApplication::where('company', 'GitHub')->first();
        $this->assertNotNull($application);
        $this->assertEquals('applied', $application->status);

        // Move through Kanban workflow
        Livewire::test(JobTracker::class)
            ->call('updateStatus', $application->id, 'interviewing')
            ->assertNotified();

        $this->assertEquals('interviewing', $application->fresh()->status);
    }

    /**
     * Scenario 9: Public Discoverability, SEO & Contact Form Rate Limiting
     */
    public function test_scenario_09_discover_directory_seo_and_contact_rate_limiting(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'full_name' => 'Marcus Aurelius Dev',
            'headline' => 'Philosophy & Code',
            'slug' => 'marcus-dev',
            'is_published' => true,
            'is_discoverable' => true,
            'meta_description' => 'Stoic philosopher and expert software craftsman.',
        ]);

        // Discover directory search
        $this->get('/discover')
            ->assertStatus(200)
            ->assertSee('Marcus Aurelius Dev');

        // Dynamic SEO tags
        $this->get('/marcus-dev')
            ->assertStatus(200)
            ->assertSee('Stoic philosopher and expert software craftsman');

        // Contact rate limiting
        RateLimiter::clear('contact-form:127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            Livewire::withQueryParams(['slug' => 'marcus-dev'])
                ->test('contact')
                ->set('senderName', 'Recruiter')
                ->set('senderEmail', 'recruiter@tech.com')
                ->set('senderMessage', 'Great portfolio! ' . $i)
                ->call('sendMessage')
                ->assertSet('sent', true);
        }

        Livewire::withQueryParams(['slug' => 'marcus-dev'])
            ->test('contact')
            ->set('senderName', 'Spam Bot')
            ->set('senderEmail', 'bot@tech.com')
            ->set('senderMessage', 'Spamming message')
            ->call('sendMessage')
            ->assertSet('rateLimited', true);
    }

    /**
     * Scenario 10: Complete GDPR Lifecycle (Export Archive & Deletion Cascade)
     */
    public function test_scenario_10_gdpr_full_data_export_and_zero_orphan_deletion(): void
    {
        $user = User::factory()->create(['email' => 'gdpr-final@example.com']);
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create(['account_id' => $account->id, 'user_id' => $user->id, 'full_name' => 'Final GDPR User']);

        Project::create(['profile_id' => $profile->id, 'title' => 'GDPR Proj', 'slug' => 'gdpr-p', 'sort_order' => 1]);
        Skill::create(['profile_id' => $profile->id, 'name' => 'Security', 'sort_order' => 1]);
        CoverLetterGeneration::create(['profile_id' => $profile->id, 'job_title' => 'Dev', 'company_name' => 'Co', 'job_description' => 'JD', 'cover_letter' => 'Letter']);
        JobApplication::create(['profile_id' => $profile->id, 'company' => 'Co', 'role' => 'Dev', 'status' => 'applied']);
        Domain::create(['profile_id' => $profile->id, 'domain' => 'my-gdpr-site.com']);

        // 1. Export Data Archive
        $gdpr = app(GdprService::class);
        $export = $gdpr->exportAccountData($account);

        $this->assertEquals('gdpr-final@example.com', $export['owner']['email']);
        $this->assertCount(1, $export['profiles']);
        $this->assertEquals('Final GDPR User', $export['profiles'][0]['full_name']);

        // 2. Cascade Delete Account
        $gdpr->deleteAccount($account);

        $this->assertEquals(0, Account::where('id', $account->id)->count());
        $this->assertEquals(0, Profile::where('id', $profile->id)->count());
        $this->assertEquals(0, Project::count());
        $this->assertEquals(0, Skill::count());
        $this->assertEquals(0, CoverLetterGeneration::count());
        $this->assertEquals(0, JobApplication::count());
        $this->assertEquals(0, Domain::count());
    }
}

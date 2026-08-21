<?php

namespace Tests\Feature;

use App\Filament\Pages\JobTracker;
use App\Filament\Pages\ResumeImport;
use App\Filament\Resources\CoverLetterGenerations\Pages\CreateCoverLetterGeneration;
use App\Models\Account;
use App\Models\CoverLetterGeneration;
use App\Models\JobApplication;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use App\Services\CoverLetterService;
use App\Services\ResumeParserService;
use Database\Seeders\ThemeSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GrowthFeaturesTest extends TestCase
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

    public function test_resume_parser_service_extracts_and_structures_data(): void
    {
        $parser = new ResumeParserService();

        $rawText = "John Doe\nSenior Cloud Architect\nExperienced software engineer specializing in Laravel, React, and AWS cloud infrastructure.\nEmail: john@example.com\nPhone: (555) 123-4567";

        $parsed = $parser->parse($rawText);

        $this->assertEquals('John Doe', $parsed['full_name']);
        $this->assertEquals('Senior Cloud Architect', $parsed['headline']);
        $this->assertEquals('john@example.com', $parsed['email']);
        $this->assertNotEmpty($parsed['skills']);
        $this->assertNotEmpty($parsed['experiences']);
    }

    public function test_resume_import_filament_page_populates_tenant_portfolio(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id, 'plan_slug' => 'pro']);
        $profile = Profile::factory()->create(['account_id' => $account->id, 'user_id' => $user->id]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
        Filament::auth()->login($user);
        Filament::setTenant($account);

        Livewire::test(ResumeImport::class)
            ->assertSuccessful()
            ->set('resumeText', "Jane Doe\nPrincipal Engineer\nLeading backend distributed systems.\nEmail: jane@corp.com")
            ->call('parseResume')
            ->assertSet('step', 2)
            ->call('importParsedData')
            ->assertNotified();

        $this->assertEquals('Jane Doe', $profile->fresh()->full_name);
        $this->assertEquals('Principal Engineer', $profile->fresh()->headline);
        $this->assertGreaterThan(0, $profile->experiences()->count());
    }

    public function test_cover_letter_service_generates_tailored_letter(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id, 'plan_slug' => 'pro']);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'full_name' => 'Michael Chen',
            'headline' => 'Staff Backend Engineer',
        ]);

        Skill::create(['profile_id' => $profile->id, 'name' => 'Laravel', 'category' => 'Backend']);

        $service = new CoverLetterService();
        $letter = $service->generate($profile, 'Senior Infrastructure Engineer', 'Acme Corp', 'We need an expert in Laravel and high-availability architecture.');

        $this->assertStringContainsString('Acme Corp', $letter);
        $this->assertStringContainsString('Michael Chen', $letter);
    }

    public function test_cover_letter_filament_resource_creation_and_quota_check(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id, 'plan_slug' => 'pro']);
        $profile = Profile::factory()->create(['account_id' => $account->id, 'user_id' => $user->id]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
        Filament::auth()->login($user);
        Filament::setTenant($account);

        Livewire::test(CreateCoverLetterGeneration::class)
            ->fillForm([
                'job_title' => 'Lead Full-Stack Developer',
                'company_name' => 'Stripe',
                'job_description' => 'Build global financial infrastructure and payment APIs.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('cover_letter_generations', [
            'profile_id' => $profile->id,
            'job_title' => 'Lead Full-Stack Developer',
            'company_name' => 'Stripe',
        ]);

        $this->assertEquals(1, $account->fresh()->ai_generations_used_current_period);
    }

    public function test_job_tracker_kanban_board_workflow_and_isolation(): void
    {
        $userA = User::factory()->create();
        $accountA = Account::factory()->create(['owner_user_id' => $userA->id, 'plan_slug' => 'pro']);
        $profileA = Profile::factory()->create(['account_id' => $accountA->id, 'user_id' => $userA->id]);

        $jobAppA = JobApplication::create([
            'profile_id' => $profileA->id,
            'company' => 'Google',
            'role' => 'Staff Site Reliability Engineer',
            'status' => 'saved',
        ]);

        $userB = User::factory()->create();
        $accountB = Account::factory()->create(['owner_user_id' => $userB->id, 'plan_slug' => 'pro']);
        $profileB = Profile::factory()->create(['account_id' => $accountB->id, 'user_id' => $userB->id]);

        $jobAppB = JobApplication::create([
            'profile_id' => $profileB->id,
            'company' => 'Meta',
            'role' => 'Engineering Manager',
            'status' => 'saved',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($userA);
        Filament::auth()->login($userA);
        Filament::setTenant($accountA);

        Livewire::test(JobTracker::class)
            ->assertSuccessful()
            ->assertSee('Google')
            ->assertDontSee('Meta')
            ->call('updateStatus', $jobAppA->id, 'interviewing')
            ->assertNotified();

        $this->assertEquals('interviewing', $jobAppA->fresh()->status);
    }

    public function test_public_discover_directory_only_shows_discoverable_profiles(): void
    {
        $userA = User::factory()->create();
        $accountA = Account::factory()->create(['owner_user_id' => $userA->id]);
        Profile::factory()->create([
            'account_id' => $accountA->id,
            'user_id' => $userA->id,
            'full_name' => 'Alice Discoverable Developer',
            'is_published' => true,
            'is_discoverable' => true,
        ]);

        $userB = User::factory()->create();
        $accountB = Account::factory()->create(['owner_user_id' => $userB->id]);
        Profile::factory()->create([
            'account_id' => $accountB->id,
            'user_id' => $userB->id,
            'full_name' => 'Bob Private Developer',
            'is_published' => true,
            'is_discoverable' => false,
        ]);

        $this->get('/discover')
            ->assertStatus(200)
            ->assertSee('Alice Discoverable Developer')
            ->assertDontSee('Bob Private Developer');
    }
}

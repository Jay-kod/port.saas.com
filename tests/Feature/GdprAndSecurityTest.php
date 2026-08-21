<?php

namespace Tests\Feature;

use App\Filament\Pages\PrivacyAndData;
use App\Filament\Resources\PortfolioReports\Tables\PortfolioReportsTable;
use App\Models\Account;
use App\Models\CoverLetterGeneration;
use App\Models\Domain;
use App\Models\Experience;
use App\Models\JobApplication;
use App\Models\PortfolioReport;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ResumeGeneration;
use App\Models\Skill;
use App\Models\Template;
use App\Models\User;
use App\Services\GdprService;
use Database\Seeders\ThemeSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class GdprAndSecurityTest extends TestCase
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

    public function test_gdpr_data_export_payload_structure(): void
    {
        $user = User::factory()->create(['name' => 'GDPR User', 'email' => 'gdpr@example.com']);
        $account = Account::factory()->create(['owner_user_id' => $user->id, 'name' => 'GDPR Account']);
        $profile = Profile::factory()->create(['account_id' => $account->id, 'user_id' => $user->id, 'full_name' => 'Jane Doe']);

        Project::create(['profile_id' => $profile->id, 'title' => 'GDPR Project', 'slug' => 'gdpr-proj', 'sort_order' => 1]);
        Skill::create(['profile_id' => $profile->id, 'name' => 'Data Privacy', 'sort_order' => 1]);
        CoverLetterGeneration::create(['profile_id' => $profile->id, 'job_title' => 'Privacy Engineer', 'company_name' => 'SafeCorp', 'job_description' => 'JD text', 'cover_letter' => 'Hello']);
        JobApplication::create(['profile_id' => $profile->id, 'company' => 'SafeCorp', 'role' => 'Privacy Engineer', 'status' => 'applied']);

        $service = app(GdprService::class);
        $export = $service->exportAccountData($account);

        $this->assertEquals('GDPR Account', $export['account']['name']);
        $this->assertEquals('gdpr@example.com', $export['owner']['email']);
        $this->assertCount(1, $export['profiles']);
        $this->assertEquals('Jane Doe', $export['profiles'][0]['full_name']);
        $this->assertCount(1, $export['profiles'][0]['projects']);
        $this->assertCount(1, $export['profiles'][0]['skills']);
        $this->assertCount(1, $export['profiles'][0]['cover_letter_generations']);
        $this->assertCount(1, $export['profiles'][0]['job_applications']);
    }

    public function test_gdpr_account_deletion_leaves_zero_orphaned_records(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create(['account_id' => $account->id, 'user_id' => $user->id]);

        Experience::create(['profile_id' => $profile->id, 'title' => 'Software Engineer', 'company' => 'Tech Corp', 'start_date' => '2023-01-01', 'sort_order' => 1]);
        Project::create(['profile_id' => $profile->id, 'title' => 'Project', 'slug' => 'p1', 'sort_order' => 1]);
        Skill::create(['profile_id' => $profile->id, 'name' => 'PHP', 'sort_order' => 1]);
        ResumeGeneration::create(['profile_id' => $profile->id, 'job_title' => 'Dev', 'company' => 'Co', 'target_role' => 'Engineer', 'generated_data' => []]);
        CoverLetterGeneration::create(['profile_id' => $profile->id, 'job_title' => 'Dev', 'company_name' => 'Co', 'job_description' => 'JD text', 'cover_letter' => 'Text']);
        JobApplication::create(['profile_id' => $profile->id, 'company' => 'Co', 'role' => 'Dev', 'status' => 'applied']);
        Domain::create(['profile_id' => $profile->id, 'domain' => 'janedoe.com']);

        $service = app(GdprService::class);
        $service->deleteAccount($account);

        $this->assertEquals(0, Account::count());
        $this->assertEquals(0, Profile::count());
        $this->assertEquals(0, Experience::count());
        $this->assertEquals(0, Project::count());
        $this->assertEquals(0, Skill::count());
        $this->assertEquals(0, ResumeGeneration::count());
        $this->assertEquals(0, CoverLetterGeneration::count());
        $this->assertEquals(0, JobApplication::count());
        $this->assertEquals(0, Domain::count());
    }

    public function test_terms_and_privacy_legal_pages_load_successfully(): void
    {
        $this->get('/terms')->assertStatus(200)->assertSee('Terms of Service');
        $this->get('/privacy')->assertStatus(200)->assertSee('Privacy Policy');
    }

    public function test_contact_form_rate_limiting(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create(['account_id' => $account->id, 'user_id' => $user->id, 'slug' => 'contact-tester', 'is_published' => true]);

        RateLimiter::clear('contact-form:127.0.0.1');

        // 5 successful requests
        for ($i = 0; $i < 5; $i++) {
            Livewire::withQueryParams(['slug' => 'contact-tester'])
                ->test('contact')
                ->set('senderName', 'Spam Bot')
                ->set('senderEmail', 'bot@spam.com')
                ->set('senderMessage', 'Hello message ' . $i)
                ->call('sendMessage')
                ->assertSet('sent', true)
                ->assertSet('rateLimited', false);
        }

        // 6th request triggers rate limiter
        Livewire::withQueryParams(['slug' => 'contact-tester'])
            ->test('contact')
            ->set('senderName', 'Spam Bot')
            ->set('senderEmail', 'bot@spam.com')
            ->set('senderMessage', 'Blocked message')
            ->call('sendMessage')
            ->assertSet('rateLimited', true);
    }

    public function test_portfolio_abuse_reporting_and_unpublish_moderation(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'bad-portfolio',
            'is_published' => true,
        ]);

        $report = PortfolioReport::create([
            'profile_id' => $profile->id,
            'reason' => 'spam',
            'details' => 'Malicious links detected on this portfolio.',
            'reporter_ip' => '192.168.1.1',
            'status' => 'pending',
        ]);

        $this->assertTrue($profile->fresh()->is_published);
        $this->assertEquals('pending', $report->status);

        // Moderator action to unpublish
        $report->profile->update(['is_published' => false]);
        $report->update(['status' => 'reviewed']);

        $this->assertFalse($profile->fresh()->is_published);
        $this->assertEquals('reviewed', $report->fresh()->status);
    }
}

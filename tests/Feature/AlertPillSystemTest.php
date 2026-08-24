<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertPillSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $superAdmin;
    protected Account $account;
    protected Profile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_super_admin' => false]);
        $this->superAdmin = User::factory()->create(['is_super_admin' => true]);

        $this->account = Account::factory()->create([
            'owner_user_id' => $this->user->id,
            'plan_slug' => 'agency',
        ]);

        $this->profile = Profile::create([
            'account_id' => $this->account->id,
            'user_id' => $this->user->id,
            'slug' => 'dev-tester',
            'full_name' => 'Developer Tester',
            'is_published' => true,
        ]);
    }

    public function test_alert_pill_component_is_rendered_on_developer_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/developer/dashboard');

        $response->assertOk();
        $response->assertSee('id="devfolio-alert-pill-stack"', false);
        $response->assertSee('top-1/2 -translate-y-1/2', false);
        $response->assertSee('alertPillSystem', false);
    }

    public function test_alert_pill_component_is_rendered_on_agency_hub(): void
    {
        $response = $this->actingAs($this->user)->get('/agency');

        $response->assertOk();
        $response->assertSee('id="devfolio-alert-pill-stack"', false);
        $response->assertSee('top-1/2 -translate-y-1/2', false);
        $response->assertSee('alertPillSystem', false);
    }

    public function test_alert_pill_component_is_rendered_on_super_admin_master_control(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin');

        $response->assertOk();
        $response->assertSee('id="devfolio-alert-pill-stack"', false);
        $response->assertSee('top-1/2 -translate-y-1/2', false);
        $response->assertSee('alertPillSystem', false);
    }

    public function test_session_flash_success_message_is_passed_to_alert_pill_initial_state(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['success' => 'Task successfully completed!'])
            ->get('/developer/dashboard');

        $response->assertOk();
        $response->assertSee('Task successfully completed!', false);
        $response->assertSee('Task Completed', false);
    }

    public function test_session_flash_error_message_is_passed_to_alert_pill_initial_state(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['error' => 'API rate limit exceeded'])
            ->get('/developer/dashboard');

        $response->assertOk();
        $response->assertSee('API rate limit exceeded', false);
        $response->assertSee('Action Failed', false);
    }

    public function test_session_flash_warning_message_is_passed_to_alert_pill_initial_state(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['warning' => 'AI resume quota is running low'])
            ->get('/developer/dashboard');

        $response->assertOk();
        $response->assertSee('AI resume quota is running low', false);
        $response->assertSee('Attention', false);
    }

    public function test_alert_pill_has_right_middle_viewport_positioning_classes(): void
    {
        $response = $this->actingAs($this->user)->get('/developer/dashboard');

        $response->assertOk();
        // Check for right-aligned middle-screen fixed placement
        $response->assertSee('fixed right-3 sm:right-6 top-1/2 -translate-y-1/2 z-[9999]', false);
    }
}

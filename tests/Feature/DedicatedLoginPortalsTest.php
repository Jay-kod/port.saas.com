<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class DedicatedLoginPortalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ThemeSeeder::class);
    }

    public function test_developer_login_page_renders_with_emerald_tokens(): void
    {
        $response = $this->get('/developer/login');

        $response->assertOk();
        $response->assertSee('Developer Portal');
        $response->assertSee('Developer Sign In');
        $response->assertSee('developer@example.com');
        $response->assertSee('AI Resume');
    }

    public function test_developer_can_authenticate_and_redirect_to_dashboard(): void
    {
        $developer = User::factory()->create([
            'email' => 'developer@example.com',
            'password' => bcrypt('password'),
            'is_super_admin' => false,
        ]);
        $account = Account::factory()->create([
            'owner_user_id' => $developer->id,
            'plan_slug' => 'free',
        ]);
        Profile::create([
            'account_id' => $account->id,
            'user_id' => $developer->id,
            'slug' => 'dev-john',
            'full_name' => 'John Dev',
            'is_published' => true,
        ]);

        Volt::test('auth.developer-login')
            ->set('email', 'developer@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($developer);
    }

    public function test_developer_autofill_helper_fills_demo_credentials(): void
    {
        Volt::test('auth.developer-login')
            ->call('fillDemo')
            ->assertSet('email', 'developer@example.com')
            ->assertSet('password', 'password');
    }

    public function test_agency_login_page_renders_with_teal_tokens(): void
    {
        $response = $this->get('/agency/login');

        $response->assertOk();
        $response->assertSee('Agency Hub');
        $response->assertSee('Agency Sign In');
        $response->assertSee('agency@example.com');
        $response->assertSee('Multi-Client');
    }

    public function test_agency_user_can_authenticate_and_redirect_to_agency_dashboard(): void
    {
        $agency = User::factory()->create([
            'email' => 'agency@example.com',
            'password' => bcrypt('password'),
            'is_super_admin' => false,
        ]);
        $account = Account::factory()->create([
            'owner_user_id' => $agency->id,
            'plan_slug' => 'agency',
        ]);
        Profile::create([
            'account_id' => $account->id,
            'user_id' => $agency->id,
            'slug' => 'apex-agency',
            'full_name' => 'Apex Agency',
            'is_published' => true,
        ]);

        Volt::test('auth.agency-login')
            ->set('email', 'agency@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('agency'));

        $this->assertAuthenticatedAs($agency);
    }

    public function test_agency_autofill_helper_fills_demo_credentials(): void
    {
        Volt::test('auth.agency-login')
            ->call('fillDemo')
            ->assertSet('email', 'agency@example.com')
            ->assertSet('password', 'password');
    }

    public function test_super_admin_login_page_renders_with_amber_tokens(): void
    {
        $response = $this->get('/super-admin/login');

        $response->assertOk();
        $response->assertSee('ROOT ADMIN ZONE');
        $response->assertSee('Super Admin Sign In');
        $response->assertSee('admin@example.com');
        $response->assertSee('Audit Trail');
    }

    public function test_super_admin_can_authenticate_and_redirect_to_super_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
        ]);

        Volt::test('auth.super-admin-login')
            ->set('email', 'admin@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('super-admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_super_admin_autofill_helper_fills_demo_credentials(): void
    {
        Volt::test('auth.super-admin-login')
            ->call('fillDemo')
            ->assertSet('email', 'admin@example.com')
            ->assertSet('password', 'password');
    }

    public function test_super_admin_login_strictly_rejects_non_super_admins(): void
    {
        $developer = User::factory()->create([
            'email' => 'developer@example.com',
            'password' => bcrypt('password'),
            'is_super_admin' => false,
        ]);

        Volt::test('auth.super-admin-login')
            ->set('email', 'developer@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email'])
            ->assertSee('Access Restricted');

        $this->assertGuest();
    }

    public function test_unauthenticated_guests_are_redirected_to_respective_login_portals(): void
    {
        // Unauthenticated access to /super-admin should redirect to /super-admin/login
        $this->get('/super-admin')->assertRedirect(route('super-admin.login'));

        // Unauthenticated access to /agency should redirect to /agency/login
        $this->get('/agency')->assertRedirect(route('agency.login'));

        // Unauthenticated access to /developer/dashboard should redirect to /developer/login
        $this->get('/developer/dashboard')->assertRedirect(route('developer.login'));
    }

    public function test_logout_redirects_appropriately(): void
    {
        $developer = User::factory()->create([
            'is_super_admin' => false,
        ]);

        $this->actingAs($developer)
            ->post('/logout')
            ->assertRedirect(route('developer.login'));

        $this->assertGuest();
    }

    public function test_marketing_header_and_homepage_cta_directs_to_developer_login_and_has_all_portals(): void
    {
        config(['saas.mode' => true]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('developer.login'));
        $response->assertSee(route('agency.login'));
        $response->assertSee(route('super-admin.login'));
        $response->assertSee('Get Started');
    }

    public function test_forgot_password_page_renders_and_processes_request(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertOk();
        $response->assertSee('Reset your password');
        $response->assertSee('Send Reset Instructions');

        Volt::test('auth.forgot-password')
            ->set('email', 'developer@example.com')
            ->call('sendResetLink')
            ->assertHasNoErrors()
            ->assertSet('status', 'If an account exists with that email address, a password reset link has been dispatched.');
    }

    public function test_developer_and_agency_login_pages_contain_forgot_password_and_password_eye_toggle(): void
    {
        $devResponse = $this->get('/developer/login');
        $devResponse->assertOk();
        $devResponse->assertSee('Forgot password?');
        $devResponse->assertSee(route('password.request'));
        $devResponse->assertSee('Toggle password visibility');

        $agencyResponse = $this->get('/agency/login');
        $agencyResponse->assertOk();
        $agencyResponse->assertSee('Forgot password?');
        $agencyResponse->assertSee(route('password.request'));
        $agencyResponse->assertSee('Toggle password visibility');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MultiUserRoleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ThemeSeeder::class);
    }

    public function test_developer_login_redirects_to_user_dashboard(): void
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
            'slug' => 'dev-user',
            'full_name' => 'Developer User',
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

    public function test_agency_login_redirects_to_agency_dashboard(): void
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
            'slug' => 'agency-client',
            'full_name' => 'Agency Client',
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

    public function test_super_admin_login_redirects_to_super_admin_dashboard(): void
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

    public function test_admin_login_route_redirects_to_super_admin_login(): void
    {
        $response = $this->get('/admin/login');
        $response->assertRedirect(route('super-admin.login'));
    }
}

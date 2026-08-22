<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\Account;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MultiUserRoleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ThemeSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $router = app('router');
        require base_path('routes/web.php');
        $router->getRoutes()->refreshNameLookups();
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

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'developer@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
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

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'agency@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
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
        $account = Account::factory()->create([
            'owner_user_id' => $admin->id,
            'plan_slug' => 'agency',
        ]);
        Profile::create([
            'account_id' => $account->id,
            'user_id' => $admin->id,
            'slug' => 'super-admin-user',
            'full_name' => 'Super Admin User',
            'is_published' => true,
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect(route('super-admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_role_selector_updates_form_state_accurately(): void
    {
        $component = Livewire::test(Login::class);

        // Default on mount should be developer
        $component->assertFormSet([
            'email' => 'developer@example.com',
            'password' => 'password',
        ]);

        // Switch to admin / agency
        $component->call('selectRole', 'admin', 'agency@example.com')
            ->assertSet('selectedRole', 'admin')
            ->assertFormSet([
                'email' => 'agency@example.com',
                'password' => 'password',
            ]);

        // Switch to super admin
        $component->call('selectRole', 'super_admin', 'admin@example.com')
            ->assertSet('selectedRole', 'super_admin')
            ->assertFormSet([
                'email' => 'admin@example.com',
                'password' => 'password',
            ]);
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Register;
use App\Models\Account;
use App\Models\Profile;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

/**
 * Phase 2 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * Tests self-service registration, auto-provisioning of Account & Profile,
 * unique slug generation, onboarding wizard, and dashboard checklist widget.
 */
class RegistrationAndOnboardingTest extends TestCase
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
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_user_registration_provisions_account_and_unpublished_profile(): void
    {
        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'John Developer',
                'email' => 'john@example.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('John Developer', $user->name);

        $account = Account::query()->where('owner_user_id', $user->id)->first();
        $this->assertNotNull($account);
        $this->assertEquals('John Developer', $account->name);
        $this->assertEquals('free', $account->plan_slug);

        $profile = Profile::query()->where('account_id', $account->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals($user->id, $profile->user_id);
        $this->assertEquals('john-developer', $profile->slug);
        $this->assertEquals('John Developer', $profile->full_name);
        $this->assertFalse($profile->is_published);
    }

    public function test_duplicate_name_registration_generates_unique_slugs(): void
    {
        // First registration
        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Sarah Connor',
                'email' => 'sarah1@example.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        // Second registration with identical name
        auth()->logout();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Sarah Connor',
                'email' => 'sarah2@example.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $profile1 = Profile::query()->where('email', 'sarah1@example.com')->orWhereHas('user', fn ($q) => $q->where('email', 'sarah1@example.com'))->first();
        $profile2 = Profile::query()->where('email', 'sarah2@example.com')->orWhereHas('user', fn ($q) => $q->where('email', 'sarah2@example.com'))->first();

        $this->assertNotNull($profile1);
        $this->assertNotNull($profile2);
        $this->assertEquals('sarah-connor', $profile1->slug);
        $this->assertEquals('sarah-connor-1', $profile2->slug);
        $this->assertNotEquals($profile1->slug, $profile2->slug);
    }

    public function test_onboarding_wizard_requires_authentication(): void
    {
        $this->get(route('onboarding'))->assertRedirect();
    }

    public function test_onboarding_wizard_updates_profile_and_publishes(): void
    {
        $user = User::factory()->create(['name' => 'Dev Person']);
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => 'dev-person',
            'is_published' => false,
        ]);

        $theme = Theme::query()->first();

        $this->actingAs($user);

        Volt::test('onboarding')
            ->set('slug', 'dev-superstar')
            ->set('full_name', 'Dev Superstar')
            ->set('headline', 'Senior AI Engineer')
            ->set('bio', 'Building futuristic intelligent applications.')
            ->set('location', 'Remote')
            ->set('selected_theme_id', $theme->id)
            ->call('saveAndFinish')
            ->assertHasNoErrors()
            ->assertRedirect('/admin');

        $profile->refresh();
        $this->assertEquals('dev-superstar', $profile->slug);
        $this->assertEquals('Dev Superstar', $profile->full_name);
        $this->assertEquals('Senior AI Engineer', $profile->headline);
        $this->assertEquals('Building futuristic intelligent applications.', $profile->bio);
        $this->assertEquals('Remote', $profile->location);
        $this->assertTrue($profile->is_published);
    }

    public function test_onboarding_checklist_widget_calculates_progress_correctly(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'is_published' => false,
        ]);

        $this->actingAs($user);
        Filament::setTenant($account);

        $widget = new \App\Filament\Widgets\OnboardingChecklistWidget();
        $data = $widget->getViewData();

        $this->assertArrayHasKey('items', $data);
        $this->assertCount(4, $data['items']);
        $this->assertEquals(0, $data['completedCount']);
        $this->assertEquals(0, $data['percentage']);

        // Now publish profile
        $profile->update(['is_published' => true]);
        $dataUpdated = $widget->getViewData();
        $this->assertEquals(1, $dataUpdated['completedCount']);
        $this->assertEquals(25, $dataUpdated['percentage']);
    }
}

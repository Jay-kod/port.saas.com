<?php

namespace Tests\Feature;

use App\Exceptions\AiQuotaExceededException;
use App\Filament\Pages\BillingSettings;
use App\Models\Account;
use App\Models\AiSetting;
use App\Models\Profile;
use App\Models\User;
use App\Services\AiUsageGuard;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillingAndUsageMeteringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_free_plan_account_is_blocked_after_reaching_quota(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'plan_slug' => 'free',
            'ai_generations_used_current_period' => 3,
        ]);

        $guard = app(AiUsageGuard::class);

        $this->expectException(AiQuotaExceededException::class);
        $guard->ensureCanGenerate($account);
    }

    public function test_free_plan_account_can_generate_under_quota_and_usage_is_recorded(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'plan_slug' => 'free',
            'ai_generations_used_current_period' => 1,
        ]);

        $guard = app(AiUsageGuard::class);

        // Should not throw
        $guard->ensureCanGenerate($account);
        $this->assertEquals(2, $guard->getRemainingGenerations($account));

        // Record generation
        $guard->recordGeneration($account);
        $account->refresh();

        $this->assertEquals(2, $account->ai_generations_used_current_period);
        $this->assertEquals(1, $guard->getRemainingGenerations($account));
    }

    public function test_byok_account_is_never_blocked_even_when_exceeding_platform_quota(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'plan_slug' => 'free',
            'ai_generations_used_current_period' => 99,
        ]);

        // Create active BYOK AiSetting for this account
        AiSetting::create([
            'account_id' => $account->id,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-test-byok-custom-key',
            'is_active' => true,
        ]);

        $guard = app(AiUsageGuard::class);

        $this->assertTrue($guard->isByokActive($account));
        $this->assertNull($guard->getRemainingGenerations($account));

        // Should not throw despite 99 used generations on free tier
        $guard->ensureCanGenerate($account);
        $this->assertTrue(true);
    }

    public function test_pro_plan_account_has_unlimited_generations(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'plan_slug' => 'pro',
            'ai_generations_used_current_period' => 50,
        ]);

        $guard = app(AiUsageGuard::class);

        $this->assertNull($guard->getRemainingGenerations($account));

        // Should not throw
        $guard->ensureCanGenerate($account);
        $this->assertTrue(true);
    }

    public function test_account_model_uses_billable_trait_and_provides_stripe_helpers(): void
    {
        $user = User::factory()->create(['email' => 'dev@company.com']);
        $account = Account::factory()->create([
            'name' => 'Acme Dev Corp',
            'owner_user_id' => $user->id,
        ]);

        $this->assertEquals('dev@company.com', $account->stripeEmail());
        $this->assertEquals('Acme Dev Corp', $account->stripeName());
    }

    public function test_filament_billing_settings_page_renders_for_tenant(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $user->id,
            'plan_slug' => 'free',
            'ai_generations_used_current_period' => 1,
        ]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
        Filament::auth()->login($user);
        Filament::setTenant($account);

        Livewire::test(BillingSettings::class)
            ->assertSuccessful()
            ->assertSee('Billing & Usage')
            ->assertSee('Free')
            ->assertSee('Pro Developer')
            ->assertSee('Agency / Team');
    }
}

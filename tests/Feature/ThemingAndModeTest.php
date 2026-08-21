<?php

namespace Tests\Feature;

use App\Filament\Pages\ThemeSelector;
use App\Models\Account;
use App\Models\Profile;
use App\Models\Theme;
use App\Models\User;
use App\Services\ThemeService;
use Database\Seeders\ThemeSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ThemingAndModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ThemeSeeder::class);
    }

    public function test_theme_seeder_creates_seven_dual_mode_themes_with_complete_tokens(): void
    {
        $themes = Theme::all();
        $this->assertCount(7, $themes);

        $expectedTokens = [
            'background', 'surface', 'primary', 'secondary', 'accent',
            'text', 'text_muted', 'border', 'success',
        ];

        foreach ($themes as $theme) {
            $this->assertIsArray($theme->colors);
            $this->assertArrayHasKey('dark', $theme->colors, "Theme {$theme->name} missing dark palette");
            $this->assertArrayHasKey('light', $theme->colors, "Theme {$theme->name} missing light palette");

            foreach ($expectedTokens as $token) {
                $this->assertArrayHasKey($token, $theme->colors['dark'], "Theme {$theme->name} dark missing {$token}");
                $this->assertArrayHasKey($token, $theme->colors['light'], "Theme {$theme->name} light missing {$token}");
            }
        }
    }

    public function test_theme_service_outputs_both_dark_and_light_css_selectors(): void
    {
        $service = app(ThemeService::class);
        $css = $service->getCssVariableString();

        $this->assertStringContainsString(':root, [data-theme-mode="dark"]', $css);
        $this->assertStringContainsString('[data-theme-mode="light"]', $css);
        $this->assertStringContainsString('--color-background:', $css);
        $this->assertStringContainsString('--color-primary:', $css);
    }

    public function test_theme_service_respects_profile_custom_theme(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $oceanTheme = Theme::where('slug', 'ocean')->firstOrFail();

        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'theme_id' => $oceanTheme->id,
            'theme_mode_default' => 'light',
        ]);

        $service = app(ThemeService::class);

        $this->assertEquals('ocean', $service->getActiveTheme($profile)->slug);
        $colors = $service->getColors($profile);
        $this->assertEquals($oceanTheme->colors['light']['primary'], $colors['primary']);
    }

    public function test_theme_service_respects_preview_querystring(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $cyberTheme = Theme::where('slug', 'cyber-matrix')->firstOrFail();

        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'theme_id' => $cyberTheme->id,
        ]);

        $service = app(ThemeService::class);

        // Without query param
        $this->assertEquals('cyber-matrix', $service->getActiveTheme($profile)->slug);

        // With ?preview_theme=warm-editorial
        request()->merge(['preview_theme' => 'warm-editorial']);
        $this->assertEquals('warm-editorial', $service->getActiveTheme($profile)->slug);
    }

    public function test_filament_theme_selector_page_renders_and_updates_theme(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
        ]);

        $slateTheme = Theme::where('slug', 'slate-professional')->firstOrFail();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
        Filament::auth()->login($user);
        Filament::setTenant($account);

        Livewire::test(ThemeSelector::class)
            ->assertSuccessful()
            ->assertSee('Theme & Appearance')
            ->assertSee('Slate Professional')
            ->call('selectTheme', $slateTheme->id)
            ->set('themeModeDefault', 'dark')
            ->call('save')
            ->assertNotified();

        $profile->refresh();
        $this->assertEquals($slateTheme->id, $profile->theme_id);
        $this->assertEquals('dark', $profile->theme_mode_default);
    }
}

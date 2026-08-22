<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialiteOAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_oauth_redirect_returns_redirect_for_supported_providers(): void
    {
        $response = $this->get('/auth/redirect/github');
        $response->assertStatus(302);

        $response = $this->get('/auth/redirect/google');
        $response->assertStatus(302);
    }

    public function test_oauth_redirect_aborts_for_unsupported_provider(): void
    {
        $response = $this->get('/auth/redirect/unsupported');
        $response->assertStatus(404);
    }

    public function test_oauth_callback_creates_new_user_account_and_profile(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUserContract::class);
        $socialiteUser->shouldReceive('getId')->andReturn('gh_123456');
        $socialiteUser->shouldReceive('getEmail')->andReturn('newdeveloper@github.com');
        $socialiteUser->shouldReceive('getName')->andReturn('GitHub Developer');
        $socialiteUser->shouldReceive('getNickname')->andReturn('ghdev');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://avatars.githubusercontent.com/u/123456');

        $providerMock = Mockery::mock(SocialiteProvider::class);
        $providerMock->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('github')->andReturn($providerMock);

        $response = $this->get('/auth/callback/github');

        // New user should be redirected to onboarding
        $response->assertRedirect('/onboarding');

        // Assert database records
        $this->assertDatabaseHas('users', [
            'email' => 'newdeveloper@github.com',
            'github_id' => 'gh_123456',
        ]);

        $user = User::where('email', 'newdeveloper@github.com')->first();
        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('accounts', [
            'owner_user_id' => $user->id,
            'plan_slug' => 'free',
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'email' => 'newdeveloper@github.com',
            'is_published' => false,
        ]);
    }

    public function test_oauth_callback_authenticates_existing_user_and_updates_id(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'github_id' => null,
        ]);

        $account = Account::factory()->create(['owner_user_id' => $user->id]);
        Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'is_published' => true,
        ]);

        $socialiteUser = Mockery::mock(SocialiteUserContract::class);
        $socialiteUser->shouldReceive('getId')->andReturn('gh_987654');
        $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
        $socialiteUser->shouldReceive('getNickname')->andReturn('existing');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://avatars.githubusercontent.com/u/987654');

        $providerMock = Mockery::mock(SocialiteProvider::class);
        $providerMock->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('github')->andReturn($providerMock);

        $response = $this->get('/auth/callback/github');

        // Existing user redirects to dashboard
        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'github_id' => 'gh_987654',
        ]);
    }

    public function test_oauth_settings_page_renders_and_saves_keys_to_database(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);
        $account = Account::factory()->create(['owner_user_id' => $user->id]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Filament\Pages\OAuthSettings::class, ['tenant' => $account])
            ->assertSuccessful()
            ->set('github_client_id', 'test_gh_client_id')
            ->set('github_client_secret', 'test_gh_client_secret')
            ->set('google_client_id', 'test_google_client_id')
            ->set('google_client_secret', 'test_google_client_secret')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('oauth_settings', [
            'provider' => 'github',
            'client_id' => 'test_gh_client_id',
        ]);

        $this->assertDatabaseHas('oauth_settings', [
            'provider' => 'google',
            'client_id' => 'test_google_client_id',
        ]);

        $credentials = \App\Models\OauthSetting::getCredentials('github');
        $this->assertEquals('test_gh_client_id', $credentials['client_id']);
        $this->assertEquals('test_gh_client_secret', $credentials['client_secret']);
    }
}

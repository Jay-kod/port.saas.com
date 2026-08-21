<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * Supported OAuth providers.
     */
    /**
     * Dynamically inject OAuth credentials from DB settings or config.
     */
    public static function configureProvider(string $provider): bool
    {
        $credentials = \App\Models\OauthSetting::getCredentials($provider);

        if ($credentials) {
            config([
                "services.{$provider}.client_id" => $credentials['client_id'],
                "services.{$provider}.client_secret" => $credentials['client_secret'],
                "services.{$provider}.redirect" => url("/auth/callback/{$provider}"),
            ]);

            return (bool) ($credentials['is_enabled'] ?? true);
        }

        return false;
    }

    /**
     * Redirect the user to the OAuth provider authentication page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->allowedProviders)) {
            abort(404, 'Unsupported OAuth provider.');
        }

        $isEnabled = static::configureProvider($provider);

        if (! $isEnabled) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', ucfirst($provider) . ' sign-in is not yet configured or is disabled.');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the OAuth provider and authenticate.
     */
    public function callback(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->allowedProviders)) {
            abort(404, 'Unsupported OAuth provider.');
        }

        static::configureProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $e) {
            Log::warning("OAuth callback failed for provider {$provider}: " . $e->getMessage());

            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Unable to authenticate with ' . ucfirst($provider) . '. Please try again.');
        }

        $email = $socialUser->getEmail();
        $providerId = $socialUser->getId();
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'Developer';
        $avatar = $socialUser->getAvatar();

        // 1. Check if user exists by provider_id or by email
        $user = User::query()
            ->where($provider . '_id', $providerId)
            ->when($email, fn ($query) => $query->orWhere('email', $email))
            ->first();

        $isNewUser = false;

        if (! $user) {
            $isNewUser = true;

            // 2. Register New User
            $user = User::create([
                'name' => $name,
                'email' => $email ?? ($provider . '_' . $providerId . '@users.devfolio.ai'),
                'password' => bcrypt(Str::random(32)),
                $provider . '_id' => $providerId,
                'avatar' => $avatar,
            ]);

            // Automatically provision default Account
            $account = Account::create([
                'name' => $name . "'s Workspace",
                'owner_user_id' => $user->id,
                'plan_slug' => 'free',
            ]);

            // Generate unique slug
            $baseSlug = Str::slug($name);
            if (empty($baseSlug)) {
                $baseSlug = 'user';
            }
            $slug = $baseSlug . '-' . Str::lower(Str::random(5));

            // Create initial unpublished Profile
            Profile::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'slug' => $slug,
                'full_name' => $name,
                'email' => $user->email,
                'avatar_url' => $avatar,
                'is_published' => false,
            ]);
        } else {
            // Update provider ID and avatar if needed
            $updates = [];
            if (! $user->{$provider . '_id'}) {
                $updates[$provider . '_id'] = $providerId;
            }
            if ($avatar && ! $user->avatar) {
                $updates['avatar'] = $avatar;
            }
            if (! empty($updates)) {
                $user->update($updates);
            }
        }

        Auth::login($user, remember: true);

        // If new user, direct them to onboarding wizard, otherwise admin panel
        if ($isNewUser) {
            return redirect('/onboarding');
        }

        return redirect()->intended('/admin');
    }
}

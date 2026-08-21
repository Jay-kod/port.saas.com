<?php

namespace App\Filament\Pages\Auth;

use App\Models\Account;
use App\Models\Profile;
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Phase 2 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * Automatically provisions an Account and an initial unpublished Profile
 * with a collision-resistant unique slug upon self-service user registration.
 */
class Register extends BaseRegister
{
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        $account = Account::create([
            'name' => $user->name,
            'owner_user_id' => $user->id,
            'plan_slug' => 'free',
        ]);

        $slug = Str::slug($user->name) ?: 'portfolio';
        $original = $slug;
        $i = 1;
        while (Profile::query()->where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }

        Profile::create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'slug' => $slug,
            'full_name' => $user->name,
            'is_published' => false,
        ]);

        return $user;
    }
}

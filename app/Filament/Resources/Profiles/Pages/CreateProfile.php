<?php

namespace App\Filament\Resources\Profiles\Pages;

use App\Filament\Resources\Profiles\ProfileResource;
use App\Models\Profile;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;

    protected function beforeCreate(): void
    {
        /** @var \App\Models\Account|null $account */
        $account = \Filament\Facades\Filament::getTenant();

        if ($account && ! $account->canCreateProfile()) {
            $max = config('plans.' . ($account->plan_slug ?: 'free') . '.max_profiles', 1);

            \Filament\Notifications\Notification::make()
                ->title('Profile Limit Reached')
                ->body("Your current plan allows up to {$max} portfolio profile(s). Upgrade to the Agency plan to manage multiple profiles.")
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    /**
     * account_id is set automatically by Filament's tenancy machinery
     * (Profile::account() matches the default ownership relationship
     * name). user_id and slug are not tenancy concerns Filament knows
     * about, so we fill them in here.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['account_id'] ??= \Filament\Facades\Filament::getTenant()?->id;
        $data['user_id'] ??= Auth::id();

        if (empty($data['slug'])) {
            $base = Str::slug($data['full_name'] ?? 'portfolio') ?: 'portfolio';
            $slug = $base;
            $i = 1;
            while (Profile::query()->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }
}

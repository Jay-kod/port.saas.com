<?php

namespace App\Filament\Resources\Concerns;

/**
 * Pairs with ScopedToCurrentProfile on the Resource class. Fills
 * profile_id in on create, since Filament's automatic
 * tenant-association-on-create (which would normally handle this via
 * a direct BelongsTo to the tenant) is disabled for these resources —
 * see ScopedToCurrentProfile's docblock.
 */
trait CreatesScopedToCurrentProfile
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['profile_id'])) {
            $profile = static::getResource()::resolveCurrentTenantProfile();

            if ($profile) {
                $data['profile_id'] = $profile->id;
            }
        }

        return $data;
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.5:
 * implements Filament's tenancy contracts so the admin panel can scope
 * every resource to the logged-in user's Account(s).
 *
 * getTenants()/canAccessTenant() currently check ownership only
 * (`accounts.owner_user_id`) — Phase 8's agency tier introduces an
 * `account_user` pivot table for editor/viewer members, at which point
 * these two methods should also check that pivot, not just ownership.
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasDefaultTenant, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'owner_user_id');
    }

    public function memberAccounts()
    {
        return $this->belongsToMany(Account::class, 'account_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (! ($tenant instanceof Account)) {
            return false;
        }

        if ($tenant->owner_user_id === $this->id) {
            return true;
        }

        return $this->memberAccounts()->where('accounts.id', $tenant->id)->exists();
    }

    /**
     * @return array<Model>|Collection<int, Account>
     */
    public function getTenants(Panel $panel): array|Collection
    {
        return $this->accounts->merge($this->memberAccounts)->unique('id');
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->accounts()->first() ?? $this->memberAccounts()->first();
    }
}

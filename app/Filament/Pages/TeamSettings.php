<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Phase 8 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Multi-user team management for accounts with role-based permissions.
 */
class TeamSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Team Members';

    protected static ?string $title = 'Team & Members';

    protected static ?int $navigationSort = 86;

    protected string $view = 'filament.pages.team-settings';

    public static function canAccess(): bool
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();
        $user = Auth::user();

        if (! $account || ! $user) {
            return false;
        }

        if ($user->is_super_admin || $account->owner_user_id === $user->id || $account->getUserRole($user) === 'owner') {
            return true;
        }

        return false;
    }

    public string $inviteName = '';

    public string $inviteEmail = '';

    public string $inviteRole = 'editor';

    public function getAccount(): ?Account
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        return $account;
    }

    public function isOwner(): bool
    {
        $account = $this->getAccount();
        $user = Auth::user();

        return $account && $user && ($account->owner_user_id === $user->id || $account->getUserRole($user) === 'owner');
    }

    public function inviteMember(): void
    {
        if (! $this->isOwner()) {
            Notification::make()
                ->title('Permission Denied')
                ->body('Only account owners can invite team members.')
                ->danger()
                ->send();

            return;
        }

        $this->validate([
            'inviteName' => 'required|string|max:255',
            'inviteEmail' => 'required|email|max:255',
            'inviteRole' => 'required|in:owner,editor,viewer',
        ]);

        $account = $this->getAccount();

        if (! $account) {
            return;
        }

        // Find or create user
        $user = User::query()->where('email', $this->inviteEmail)->first();

        if (! $user) {
            $user = User::create([
                'name' => $this->inviteName,
                'email' => $this->inviteEmail,
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        // Check if already a member
        if ($account->members()->where('users.id', $user->id)->exists() || $account->owner_user_id === $user->id) {
            Notification::make()
                ->title('User Already in Team')
                ->body("{$this->inviteEmail} is already a member of this account.")
                ->warning()
                ->send();

            return;
        }

        $account->members()->attach($user->id, ['role' => $this->inviteRole]);

        Notification::make()
            ->title('Team Member Added')
            ->body("{$user->name} ({$user->email}) was added as {$this->inviteRole}.")
            ->success()
            ->send();

        $this->inviteName = '';
        $this->inviteEmail = '';
        $this->inviteRole = 'editor';
    }

    public function updateMemberRole(int $userId, string $newRole): void
    {
        if (! $this->isOwner()) {
            Notification::make()->title('Permission Denied')->danger()->send();

            return;
        }

        $account = $this->getAccount();
        $account?->members()->updateExistingPivot($userId, ['role' => $newRole]);

        Notification::make()->title('Role Updated')->success()->send();
    }

    public function removeMember(int $userId): void
    {
        if (! $this->isOwner()) {
            Notification::make()->title('Permission Denied')->danger()->send();

            return;
        }

        $account = $this->getAccount();
        $account?->members()->detach($userId);

        Notification::make()->title('Member Removed')->success()->send();
    }

    public function getMembersProperty()
    {
        $account = $this->getAccount();

        return $account?->members ?? collect();
    }
}

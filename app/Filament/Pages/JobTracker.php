<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\JobApplication;
use App\Models\Profile;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Phase 7.3 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Visual 5-column Kanban board for tracking job applications.
 */
class JobTracker extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?string $navigationLabel = 'Job Tracker';

    protected static ?string $title = 'Job Application Tracker';

    protected static ?int $navigationSort = 38;

    protected string $view = 'filament.pages.job-tracker';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $company = '';

    public string $role = '';

    public string $job_url = '';

    public string $salary_range = '';

    public string $status = 'saved';

    public string $notes = '';

    public function getAccount(): ?Account
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        return $account;
    }

    public function getProfile(): ?Profile
    {
        $account = $this->getAccount();

        return $account?->profiles()->first() ?? Profile::query()->first();
    }

    public function getApplicationsProperty()
    {
        $profile = $this->getProfile();

        if (! $profile) {
            return collect();
        }

        return JobApplication::query()
            ->where('profile_id', $profile->id)
            ->with(['resumeGeneration', 'coverLetterGeneration'])
            ->latest()
            ->get();
    }

    public function getColumnsProperty(): array
    {
        $apps = $this->applications;

        return [
            'saved' => [
                'title' => 'Bookmarked',
                'badge_color' => 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700',
                'accent' => 'border-gray-400',
                'items' => $apps->where('status', 'saved'),
            ],
            'applied' => [
                'title' => 'Applied',
                'badge_color' => 'bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border-blue-300 dark:border-blue-800',
                'accent' => 'border-blue-500',
                'items' => $apps->where('status', 'applied'),
            ],
            'interviewing' => [
                'title' => 'Interviewing',
                'badge_color' => 'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                'accent' => 'border-amber-500',
                'items' => $apps->where('status', 'interviewing'),
            ],
            'offer' => [
                'title' => 'Offer Received',
                'badge_color' => 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800',
                'accent' => 'border-emerald-500',
                'items' => $apps->where('status', 'offer'),
            ],
            'rejected' => [
                'title' => 'Archived',
                'badge_color' => 'bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-800',
                'accent' => 'border-rose-500',
                'items' => $apps->where('status', 'rejected'),
            ],
        ];
    }

    public function openAddModal(string $defaultStatus = 'saved'): void
    {
        $this->resetForm();
        $this->status = $defaultStatus;
        $this->showModal = true;
    }

    public function editApplication(int $id): void
    {
        $profile = $this->getProfile();
        $app = JobApplication::where('profile_id', $profile?->id)->find($id);

        if ($app) {
            $this->editingId = $app->id;
            $this->company = $app->company;
            $this->role = $app->role;
            $this->job_url = $app->job_url ?? '';
            $this->salary_range = $app->salary_range ?? '';
            $this->status = $app->status;
            $this->notes = $app->notes ?? '';
            $this->showModal = true;
        }
    }

    public function saveApplication(): void
    {
        $this->validate([
            'company' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'status' => 'required|in:saved,applied,interviewing,offer,rejected',
        ]);

        $profile = $this->getProfile();

        if (! $profile) {
            return;
        }

        if ($this->editingId) {
            $app = JobApplication::where('profile_id', $profile->id)->find($this->editingId);
            $app?->update([
                'company' => $this->company,
                'role' => $this->role,
                'job_url' => $this->job_url ?: null,
                'salary_range' => $this->salary_range ?: null,
                'status' => $this->status,
                'notes' => $this->notes ?: null,
            ]);

            Notification::make()->title('Application Updated')->success()->send();
        } else {
            JobApplication::create([
                'profile_id' => $profile->id,
                'company' => $this->company,
                'role' => $this->role,
                'job_url' => $this->job_url ?: null,
                'salary_range' => $this->salary_range ?: null,
                'status' => $this->status,
                'applied_at' => $this->status === 'applied' ? now() : null,
                'notes' => $this->notes ?: null,
            ]);

            Notification::make()->title('Application Created')->success()->send();
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function updateStatus(int $id, string $newStatus): void
    {
        $profile = $this->getProfile();
        $app = JobApplication::where('profile_id', $profile?->id)->find($id);

        if ($app) {
            $update = ['status' => $newStatus];
            if ($newStatus === 'applied' && ! $app->applied_at) {
                $update['applied_at'] = now();
            }

            $app->update($update);

            Notification::make()
                ->title("Moved to " . ucfirst($newStatus))
                ->success()
                ->send();
        }
    }

    public function deleteApplication(int $id): void
    {
        $profile = $this->getProfile();
        $app = JobApplication::where('profile_id', $profile?->id)->find($id);

        if ($app) {
            $app->delete();
            Notification::make()->title('Application Deleted')->success()->send();
        }
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->company = '';
        $this->role = '';
        $this->job_url = '';
        $this->salary_range = '';
        $this->status = 'saved';
        $this->notes = '';
    }
}

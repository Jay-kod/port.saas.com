<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

/**
 * Phase 9 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * GDPR data subject export and account deletion cascade service.
 */
class GdprService
{
    /**
     * Exports all data belonging to an Account into a portable JSON array.
     */
    public function exportAccountData(Account $account): array
    {
        $account->load([
            'owner',
            'members',
            'profiles.experiences',
            'profiles.projects',
            'profiles.skills',
            'profiles.certificates',
            'profiles.resumeGenerations',
            'profiles.coverLetterGenerations',
            'profiles.jobApplications',
            'profiles.domains',
            'profiles.githubSetting',
            'aiSettings',
            'templates',
        ]);

        return [
            'export_date' => now()->toIso8601String(),
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'plan' => $account->plan_slug,
                'created_at' => $account->created_at?->toIso8601String(),
                'custom_brand_name' => $account->custom_brand_name,
                'hide_platform_branding' => $account->hide_platform_branding,
            ],
            'owner' => [
                'id' => $account->owner?->id,
                'name' => $account->owner?->name,
                'email' => $account->owner?->email,
            ],
            'team_members' => $account->members->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'email' => $m->email,
                'role' => $m->pivot->role ?? 'editor',
            ])->toArray(),
            'profiles' => $account->profiles->map(fn ($p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'full_name' => $p->full_name,
                'headline' => $p->headline,
                'bio' => $p->bio,
                'email' => $p->email,
                'phone' => $p->phone,
                'location' => $p->location,
                'social_links' => $p->social_links,
                'is_published' => $p->is_published,
                'is_discoverable' => $p->is_discoverable,
                'meta_description' => $p->meta_description,
                'experiences' => $p->experiences->makeHidden(['profile_id'])->toArray(),
                'projects' => $p->projects->makeHidden(['profile_id'])->toArray(),
                'skills' => $p->skills->makeHidden(['profile_id'])->toArray(),
                'certificates' => $p->certificates->makeHidden(['profile_id'])->toArray(),
                'resume_generations' => $p->resumeGenerations->makeHidden(['profile_id'])->toArray(),
                'cover_letter_generations' => $p->coverLetterGenerations->makeHidden(['profile_id'])->toArray(),
                'job_applications' => $p->jobApplications->makeHidden(['profile_id'])->toArray(),
                'custom_domains' => $p->domains->makeHidden(['profile_id', 'verification_token'])->toArray(),
                'github_setting' => $p->githubSetting?->makeHidden(['profile_id', 'access_token'])?->toArray(),
            ])->toArray(),
            'templates' => $account->templates->makeHidden(['account_id'])->toArray(),
        ];
    }

    /**
     * Executes complete cascading deletion of an Account and all its children.
     */
    public function deleteAccount(Account $account): void
    {
        DB::transaction(function () use ($account) {
            // Delete child models of each profile
            foreach ($account->profiles as $profile) {
                $profile->experiences()->delete();
                $profile->projects()->delete();
                $profile->skills()->delete();
                $profile->certificates()->delete();
                $profile->resumeGenerations()->delete();
                $profile->coverLetterGenerations()->delete();
                $profile->jobApplications()->delete();
                $profile->domains()->delete();
                $profile->githubSetting()->delete();
                $profile->delete();
            }

            $account->aiSettings()->delete();
            $account->templates()->delete();
            $account->members()->detach();
            $account->delete();
        });
    }
}

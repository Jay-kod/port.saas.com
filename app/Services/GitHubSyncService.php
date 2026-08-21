<?php

namespace App\Services;

use App\Models\GithubSetting;
use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Pulls public repositories from GitHub and upserts them as Project
 * rows for the current profile.
 *
 * SaaS NOTE (Phase 1): reused as-is by the SaaS transformation plan.
 * The only change is that GithubSetting and the Projects it creates
 * get scoped by profile_id instead of being global. See
 * docs/agents/02-MULTI-TENANCY-FOUNDATION.md. Nothing in this class's
 * logic needs to change for that.
 */
class GitHubSyncService
{
    public function __construct(protected ?GithubSetting $setting = null)
    {
        $this->setting ??= GithubSetting::query()->first();
    }

    public function sync(): int
    {
        if (! $this->setting || ! $this->setting->username) {
            throw new RuntimeException('No GitHub username is configured under GitHub Settings.');
        }

        $response = Http::withHeaders(array_filter([
            'Accept' => 'application/vnd.github+json',
            'Authorization' => $this->setting->access_token ? 'Bearer '.$this->setting->access_token : null,
        ]))->get("https://api.github.com/users/{$this->setting->username}/repos", [
            'sort' => 'updated',
            'per_page' => 20,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Failed to fetch repositories from GitHub: '.$response->status());
        }

        $count = 0;

        foreach ($response->json() ?? [] as $repo) {
            if ($repo['fork'] ?? false) {
                continue;
            }

            Project::query()->updateOrCreate(
                ['slug' => Str::slug($repo['name'])],
                [
                    'title' => $repo['name'],
                    'summary' => $repo['description'],
                    'repo_url' => $repo['html_url'],
                    'live_url' => $repo['homepage'] ?: null,
                    'tech_stack' => array_filter([$repo['language'] ?? null]),
                ]
            );

            $count++;
        }

        $this->setting->update(['last_synced_at' => now()]);

        return $count;
    }
}

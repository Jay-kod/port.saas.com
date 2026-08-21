<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Profile;
use App\Models\Project;
use App\Models\User;
use App\Services\CurrentProfileResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.6:
 * Non-negotiable isolation test — Tenant A's data must never leak into
 * Tenant B's scoped queries.
 */
class MultiTenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_accounts_data_never_leaks_into_another_accounts_public_page(): void
    {
        // --- Tenant A ---
        $ownerA = User::factory()->create();
        $accountA = Account::factory()->create(['owner_user_id' => $ownerA->id]);
        $profileA = Profile::factory()->create([
            'account_id' => $accountA->id,
            'user_id' => $ownerA->id,
            'slug' => 'tenant-a',
        ]);
        Project::factory()->create([
            'profile_id' => $profileA->id,
            'title' => 'Tenant A Project',
        ]);

        // --- Tenant B ---
        $ownerB = User::factory()->create();
        $accountB = Account::factory()->create(['owner_user_id' => $ownerB->id]);
        $profileB = Profile::factory()->create([
            'account_id' => $accountB->id,
            'user_id' => $ownerB->id,
            'slug' => 'tenant-b',
        ]);
        Project::factory()->create([
            'profile_id' => $profileB->id,
            'title' => 'Tenant B Project',
        ]);

        // Pin the resolver to Tenant A
        $resolver = app(CurrentProfileResolver::class);
        $resolver->setResolved($profileA);

        $titles = Project::query()->pluck('title');
        $this->assertTrue($titles->contains('Tenant A Project'));
        $this->assertFalse($titles->contains('Tenant B Project'));

        // Now pin to Tenant B and verify the inverse
        $resolver->setResolved($profileB);

        // Must re-query because the global scope reads from the resolver
        // at query execution time, not at scope-registration time.
        $titles = Project::query()->pluck('title');
        $this->assertTrue($titles->contains('Tenant B Project'));
        $this->assertFalse($titles->contains('Tenant A Project'));
    }
}

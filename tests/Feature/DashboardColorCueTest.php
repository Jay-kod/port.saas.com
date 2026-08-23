<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardColorCueTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_owner_dashboard_renders_green_brand_color_cues(): void
    {
        $owner = User::factory()->create(['is_super_admin' => false]);
        $account = Account::factory()->create([
            'owner_user_id' => $owner->id,
            'plan_slug' => 'free',
        ]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $owner->id,
            'slug' => 'johndoe',
        ]);

        $response = $this->actingAs($owner)->get('/developer/dashboard');

        $response->assertStatus(200);
        // Assert Green CSS Tokens
        $response->assertSee('--panel-accent: #16A34A', false);
        $response->assertSee('--panel-accent-dark: #22C55E', false);
        $response->assertSee('Portfolio Owner');
    }

    public function test_agency_owner_dashboard_renders_teal_brand_color_cues(): void
    {
        $agencyOwner = User::factory()->create(['is_super_admin' => false]);
        $account = Account::factory()->create([
            'owner_user_id' => $agencyOwner->id,
            'plan_slug' => 'agency',
        ]);
        $profile = Profile::factory()->create([
            'account_id' => $account->id,
            'user_id' => $agencyOwner->id,
            'slug' => 'agencyhq',
        ]);

        // Agency Hub route
        $response = $this->actingAs($agencyOwner)->get('/agency');

        $response->assertStatus(200);
        // Assert Teal CSS Tokens
        $response->assertSee('--panel-accent: #0D9488', false);
        $response->assertSee('--panel-accent-dark: #14B8A6', false);
        $response->assertSee('Agency Owner');
    }

    public function test_team_member_dashboard_renders_slate_blue_color_cues(): void
    {
        $owner = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $owner->id,
            'plan_slug' => 'agency',
        ]);

        $editor = User::factory()->create(['is_super_admin' => false]);
        $account->members()->attach($editor->id, ['role' => 'editor']);

        $response = $this->actingAs($editor)->get('/developer/dashboard');

        $response->assertStatus(200);
        // Assert Slate Blue CSS Tokens
        $response->assertSee('--panel-accent: #475569', false);
        $response->assertSee('--panel-accent-dark: #64748B', false);
        $response->assertSee('Editor Seat');
    }

    public function test_super_admin_master_control_renders_amber_orange_color_cues(): void
    {
        $superAdmin = User::factory()->create([
            'is_super_admin' => true,
        ]);
        $account = Account::factory()->create([
            'owner_user_id' => $superAdmin->id,
            'plan_slug' => 'agency',
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin');

        $response->assertStatus(200);
        // Assert Amber / Orange CSS Tokens
        $response->assertSee('--panel-accent: #D97706', false);
        $response->assertSee('--panel-accent-dark: #F59E0B', false);
        $response->assertSee('SUPER ADMIN MASTER CONTROL');
        $response->assertSee('TIER 0 / ROOT ELEVATED');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Certificate;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds a single demo Profile with sample content, matching the
 * single-tenant ("the" profile) shape the app has today.
 *
 * Phase 1: creates an Account owned by the first User, attaches the
 * Profile to it with a slug, and sets profile_id on all child records.
 */
class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        if (Profile::query()->exists()) {
            return;
        }

        $owner = User::query()->first();

        if (! $owner) {
            return;
        }

        $account = Account::query()->firstOrCreate(
            ['owner_user_id' => $owner->id],
            ['name' => 'Default Account']
        );

        $profile = Profile::create([
            'account_id' => $account->id,
            'user_id' => $owner->id,
            'slug' => 'alex-doe',
            'full_name' => 'Alex Doe',
            'headline' => 'Full-Stack Developer & AI Enthusiast',
            'bio' => 'I build web applications with Laravel, Livewire and a healthy obsession with clean code.',
            'email' => 'alex@example.com',
            'phone' => null,
            'location' => 'Remote',
            'social_links' => [
                'github' => 'https://github.com/alexdoe',
                'linkedin' => 'https://linkedin.com/in/alexdoe',
            ],
            'is_published' => true,
        ]);

        Experience::create([
            'profile_id' => $profile->id,
            'title' => 'Senior Software Engineer',
            'company' => 'Acme Corp',
            'location' => 'Remote',
            'start_date' => now()->subYears(2),
            'end_date' => null,
            'is_current' => true,
            'description' => 'Leading development of internal tooling and customer-facing web apps.',
            'sort_order' => 1,
        ]);

        Project::create([
            'profile_id' => $profile->id,
            'title' => 'AI Portfolio Platform',
            'slug' => 'ai-portfolio-platform',
            'summary' => 'A self-hosted portfolio + AI resume tailoring platform.',
            'description' => 'Built with Laravel, Livewire/Volt, Filament and DomPDF.',
            'tech_stack' => ['Laravel', 'Livewire', 'Filament', 'Alpine.js'],
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        Skill::create([
            'profile_id' => $profile->id,
            'name' => 'Laravel',
            'category' => 'Backend',
            'proficiency' => 90,
            'sort_order' => 1,
        ]);

        Certificate::create([
            'profile_id' => $profile->id,
            'title' => 'AWS Certified Developer',
            'slug' => 'aws-certified-developer',
            'issuer' => 'Amazon Web Services',
            'issue_date' => now()->subYear(),
            'sort_order' => 1,
        ]);
    }
}

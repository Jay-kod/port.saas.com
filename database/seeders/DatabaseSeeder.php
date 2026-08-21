<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );

        // 2. Developer / User
        $developer = User::firstOrCreate(
            ['email' => 'developer@example.com'],
            [
                'name' => 'Demo Developer',
                'password' => bcrypt('password'),
            ]
        );

        $devAccount = \App\Models\Account::firstOrCreate(
            ['owner_user_id' => $developer->id],
            [
                'name' => 'Developer Workspace',
                'plan_slug' => 'free',
            ]
        );

        \App\Models\Profile::firstOrCreate(
            ['user_id' => $developer->id],
            [
                'account_id' => $devAccount->id,
                'slug' => 'demo-developer',
                'full_name' => 'Demo Developer',
                'headline' => 'Full-Stack Software Engineer',
                'bio' => 'Building elegant scalable software.',
                'email' => 'developer@example.com',
                'is_published' => true,
            ]
        );

        // 3. Account Admin / Agency
        $agencyUser = User::firstOrCreate(
            ['email' => 'agency@example.com'],
            [
                'name' => 'Agency Admin',
                'password' => bcrypt('password'),
            ]
        );

        $agencyAccount = \App\Models\Account::firstOrCreate(
            ['owner_user_id' => $agencyUser->id],
            [
                'name' => 'Apex Digital Agency',
                'plan_slug' => 'agency',
            ]
        );

        \App\Models\Profile::firstOrCreate(
            ['user_id' => $agencyUser->id],
            [
                'account_id' => $agencyAccount->id,
                'slug' => 'apex-agency',
                'full_name' => 'Apex Agency Team',
                'headline' => 'Digital Creative & Engineering Agency',
                'bio' => 'Crafting high-impact web applications for clients worldwide.',
                'email' => 'agency@example.com',
                'is_published' => true,
            ]
        );

        $this->call([
            ThemeSeeder::class,
            TemplateSeeder::class,
            ProfileSeeder::class,
        ]);
    }
}

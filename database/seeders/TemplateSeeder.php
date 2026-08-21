<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

/**
 * Seeds the platform's resume PDF template catalog.
 *
 * SaaS NOTE (Phase 1): these remain the global catalog (account_id
 * stays NULL). Tenant-uploaded/private templates are a Phase 7+
 * concern. See docs/agents/02-MULTI-TENANCY-FOUNDATION.md.
 */
class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Modern',
                'slug' => 'modern',
                'description' => 'Clean, single-column, ATS-friendly layout.',
                'blade_view' => 'resumes.templates.modern',
                'is_active' => true,
            ],
            [
                'name' => 'Classic',
                'slug' => 'classic',
                'description' => 'Traditional two-column resume layout.',
                'blade_view' => 'resumes.templates.classic',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            Template::query()->updateOrCreate(['slug' => $template['slug']], $template);
        }
    }
}

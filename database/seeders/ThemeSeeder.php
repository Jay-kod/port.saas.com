<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Phase 5 (docs/agents/04-THEMING-DOMAINS.md):
 * Seeds the expanded 7-theme platform catalog with dual-mode
 * (dark and light) hand-tuned 9-token color maps.
 */
class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Cyber Matrix',
                'slug' => 'cyber-matrix',
                'is_active' => true,
                'is_default' => true,
                'colors' => [
                    'dark' => [
                        'background' => '#0a0e14',
                        'surface' => '#111722',
                        'primary' => '#00ff9c',
                        'secondary' => '#00c2ff',
                        'accent' => '#ff2ec4',
                        'text' => '#e6f1ff',
                        'text_muted' => '#8a9bb5',
                        'border' => '#1f2b3d',
                        'success' => '#00ff9c',
                    ],
                    'light' => [
                        'background' => '#f4f8f6',
                        'surface' => '#ffffff',
                        'primary' => '#059669',
                        'secondary' => '#0284c7',
                        'accent' => '#db2777',
                        'text' => '#0f172a',
                        'text_muted' => '#64748b',
                        'border' => '#e2e8f0',
                        'success' => '#16a34a',
                    ],
                ],
            ],
            [
                'name' => 'Bioluminescent',
                'slug' => 'bioluminescent',
                'is_active' => false,
                'is_default' => false,
                'colors' => [
                    'dark' => [
                        'background' => '#040d12',
                        'surface' => '#0b1f26',
                        'primary' => '#5efce8',
                        'secondary' => '#736efe',
                        'accent' => '#00e6c3',
                        'text' => '#e0fbfc',
                        'text_muted' => '#7fa9ac',
                        'border' => '#123539',
                        'success' => '#5efce8',
                    ],
                    'light' => [
                        'background' => '#f0fdfa',
                        'surface' => '#ffffff',
                        'primary' => '#0d9488',
                        'secondary' => '#6366f1',
                        'accent' => '#14b8a6',
                        'text' => '#0f172a',
                        'text_muted' => '#64748b',
                        'border' => '#ccfbf1',
                        'success' => '#0d9488',
                    ],
                ],
            ],
            [
                'name' => 'Toxic Cyberpunk',
                'slug' => 'toxic-cyberpunk',
                'is_active' => false,
                'is_default' => false,
                'colors' => [
                    'dark' => [
                        'background' => '#0d0d0d',
                        'surface' => '#191919',
                        'primary' => '#d4fc00',
                        'secondary' => '#ff003c',
                        'accent' => '#fc00ff',
                        'text' => '#f5f5f5',
                        'text_muted' => '#9a9a9a',
                        'border' => '#2b2b2b',
                        'success' => '#d4fc00',
                    ],
                    'light' => [
                        'background' => '#fafafa',
                        'surface' => '#ffffff',
                        'primary' => '#84cc16',
                        'secondary' => '#e11d48',
                        'accent' => '#c026d3',
                        'text' => '#18181b',
                        'text_muted' => '#71717a',
                        'border' => '#e4e4e7',
                        'success' => '#65a30d',
                    ],
                ],
            ],
            [
                'name' => 'Slate Professional',
                'slug' => 'slate-professional',
                'is_active' => false,
                'is_default' => false,
                'colors' => [
                    'dark' => [
                        'background' => '#0f172a',
                        'surface' => '#1e293b',
                        'primary' => '#38bdf8',
                        'secondary' => '#818cf8',
                        'accent' => '#f472b6',
                        'text' => '#f8fafc',
                        'text_muted' => '#94a3b8',
                        'border' => '#334155',
                        'success' => '#34d399',
                    ],
                    'light' => [
                        'background' => '#f8fafc',
                        'surface' => '#ffffff',
                        'primary' => '#0284c7',
                        'secondary' => '#4f46e5',
                        'accent' => '#db2777',
                        'text' => '#0f172a',
                        'text_muted' => '#64748b',
                        'border' => '#e2e8f0',
                        'success' => '#10b981',
                    ],
                ],
            ],
            [
                'name' => 'Warm Editorial',
                'slug' => 'warm-editorial',
                'is_active' => false,
                'is_default' => false,
                'colors' => [
                    'dark' => [
                        'background' => '#181412',
                        'surface' => '#241e1b',
                        'primary' => '#f97316',
                        'secondary' => '#eab308',
                        'accent' => '#fb7185',
                        'text' => '#fef7ee',
                        'text_muted' => '#a8998c',
                        'border' => '#3b322c',
                        'success' => '#84cc16',
                    ],
                    'light' => [
                        'background' => '#fdfbf7',
                        'surface' => '#ffffff',
                        'primary' => '#c2410c',
                        'secondary' => '#ca8a04',
                        'accent' => '#e11d48',
                        'text' => '#292524',
                        'text_muted' => '#78716c',
                        'border' => '#e7e5e4',
                        'success' => '#15803d',
                    ],
                ],
            ],
            [
                'name' => 'Ocean',
                'slug' => 'ocean',
                'is_active' => false,
                'is_default' => false,
                'colors' => [
                    'dark' => [
                        'background' => '#09131f',
                        'surface' => '#102033',
                        'primary' => '#38bdf8',
                        'secondary' => '#60a5fa',
                        'accent' => '#a78bfa',
                        'text' => '#f0f9ff',
                        'text_muted' => '#7dd3fc',
                        'border' => '#1e3a5f',
                        'success' => '#4ade80',
                    ],
                    'light' => [
                        'background' => '#f0f9ff',
                        'surface' => '#ffffff',
                        'primary' => '#0369a1',
                        'secondary' => '#2563eb',
                        'accent' => '#7c3aed',
                        'text' => '#0c4a6e',
                        'text_muted' => '#0284c7',
                        'border' => '#bae6fd',
                        'success' => '#16a34a',
                    ],
                ],
            ],
            [
                'name' => 'Classic Mono',
                'slug' => 'classic-mono',
                'is_active' => false,
                'is_default' => false,
                'colors' => [
                    'dark' => [
                        'background' => '#000000',
                        'surface' => '#121212',
                        'primary' => '#ffffff',
                        'secondary' => '#a3a3a3',
                        'accent' => '#d4d4d4',
                        'text' => '#ffffff',
                        'text_muted' => '#a3a3a3',
                        'border' => '#262626',
                        'success' => '#ffffff',
                    ],
                    'light' => [
                        'background' => '#ffffff',
                        'surface' => '#f5f5f5',
                        'primary' => '#000000',
                        'secondary' => '#525252',
                        'accent' => '#262626',
                        'text' => '#000000',
                        'text_muted' => '#737373',
                        'border' => '#e5e5e5',
                        'success' => '#000000',
                    ],
                ],
            ],
        ];

        foreach ($themes as $theme) {
            Theme::query()->updateOrCreate(['slug' => $theme['slug']], $theme);
        }
    }
}

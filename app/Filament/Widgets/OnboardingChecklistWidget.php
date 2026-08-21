<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\GithubSettings\GithubSettingResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Skills\SkillResource;
use App\Models\Account;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * Phase 2 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * Dashboard onboarding checklist widget showing steps to get started
 * with portfolio creation, github sync, and AI resume tailoring.
 */
class OnboardingChecklistWidget extends Widget
{
    protected string $view = 'filament.widgets.onboarding-checklist-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -10;

    public function getViewData(): array
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();
        $profile = $account?->profiles()->first();

        $items = [
            [
                'id' => 'onboarding',
                'title' => 'Complete profile & publish portfolio',
                'description' => 'Add your headline, bio, and turn on publishing so visitors can see your work.',
                'completed' => (bool) $profile?->is_published,
                'url' => route('onboarding'),
                'action' => 'Open Onboarding Wizard',
            ],
            [
                'id' => 'project',
                'title' => 'Add your first project',
                'description' => 'Showcase what you have built with descriptions, tags, and links.',
                'completed' => (bool) ($profile && $profile->projects()->count() > 0),
                'url' => ProjectResource::getUrl('index'),
                'action' => 'Manage Projects',
            ],
            [
                'id' => 'experience_skill',
                'title' => 'Add your skills & experience',
                'description' => 'Highlight your career journey and technical proficiencies.',
                'completed' => (bool) ($profile && ($profile->skills()->count() > 0 || $profile->experiences()->count() > 0)),
                'url' => SkillResource::getUrl('index'),
                'action' => 'Manage Skills',
            ],
            [
                'id' => 'ai_github',
                'title' => 'Connect GitHub or generate an AI resume',
                'description' => 'Sync repositories automatically or tailor your resume for job openings.',
                'completed' => (bool) ($profile && ($profile->githubSetting()->exists() || $profile->resumeGenerations()->count() > 0)),
                'url' => GithubSettingResource::getUrl('index'),
                'action' => 'Connect GitHub',
            ],
        ];

        $completedCount = count(array_filter($items, fn ($i) => $i['completed']));
        $totalCount = count($items);
        $percentage = $totalCount > 0 ? (int) round(($completedCount / $totalCount) * 100) : 0;

        return [
            'account' => $account,
            'profile' => $profile,
            'items' => $items,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'percentage' => $percentage,
        ];
    }
}

<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\Profile;
use App\Models\Project;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\Certificate;
use App\Models\ResumeGeneration;
use App\Models\CoverLetterGeneration;
use App\Models\JobApplication;
use App\Models\AiSetting;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Portfolio & Career Analytics');

state([
    'timeRange' => 'all', // all, 30d, 90d
]);

$analytics = computed(function () {
    $user = Auth::user();
    $profile = $user?->profile;
    $account = $user?->defaultTenant ?? $user?->accounts()->first();

    if (! $profile) {
        return [
            'hasProfile' => false,
            'healthScore' => 0,
            'completeness' => 0,
            'checklist' => [],
            'projects' => ['total' => 0, 'featured' => 0, 'withLiveUrl' => 0, 'withRepoUrl' => 0, 'stacks' => []],
            'skills' => ['total' => 0, 'categories' => [], 'topSkills' => [], 'avgProficiency' => 0],
            'career' => ['resumes' => 0, 'coverLetters' => 0, 'quotaLimit' => 3, 'quotaUsed' => 0, 'hasByok' => false],
            'jobFunnel' => ['total' => 0, 'stages' => [], 'interviewRate' => 0, 'offerRate' => 0, 'companies' => []],
            'seo' => ['isPublished' => false, 'isDiscoverable' => false, 'hasCustomDomain' => false, 'hasMetaDesc' => false],
            'recommendations' => [],
        ];
    }

    // 1. Profile Completeness Calculation
    $completenessItems = [
        'full_name' => ['label' => 'Full Name', 'done' => !empty($profile->full_name), 'weight' => 10],
        'headline' => ['label' => 'Professional Headline', 'done' => !empty($profile->headline), 'weight' => 15],
        'bio' => ['label' => 'Bio & Summary', 'done' => !empty($profile->bio) && strlen($profile->bio) > 30, 'weight' => 15],
        'avatar' => ['label' => 'Profile Avatar', 'done' => !empty($profile->avatar_url) || !empty($user->avatar), 'weight' => 10],
        'contact' => ['label' => 'Contact Email / Location', 'done' => !empty($profile->email) || !empty($profile->location), 'weight' => 10],
        'socials' => ['label' => 'GitHub / LinkedIn Links', 'done' => !empty($profile->social_links['github']) || !empty($profile->social_links['linkedin']), 'weight' => 10],
        'seo' => ['label' => 'SEO Meta Description', 'done' => !empty($profile->meta_description), 'weight' => 10],
        'published' => ['label' => 'Published Publicly', 'done' => (bool)$profile->is_published, 'weight' => 10],
        'discover' => ['label' => 'Discover Directory Enabled', 'done' => (bool)$profile->is_discoverable, 'weight' => 10],
    ];

    $totalWeight = 0;
    $earnedWeight = 0;
    foreach ($completenessItems as $item) {
        $totalWeight += $item['weight'];
        if ($item['done']) {
            $earnedWeight += $item['weight'];
        }
    }
    $completenessScore = $totalWeight > 0 ? (int)round(($earnedWeight / $totalWeight) * 100) : 0;

    // 2. Projects Telemetry
    $projectsQuery = Project::where('profile_id', $profile->id);
    $totalProjects = $projectsQuery->count();
    $featuredProjects = (clone $projectsQuery)->where('is_featured', true)->count();
    $liveProjects = (clone $projectsQuery)->whereNotNull('live_url')->where('live_url', '!=', '')->count();
    $repoProjects = (clone $projectsQuery)->whereNotNull('repo_url')->where('repo_url', '!=', '')->count();

    // Stacks frequency mapping
    $stacksCount = [];
    $allProjects = $projectsQuery->get();
    foreach ($allProjects as $proj) {
        if (!empty($proj->tech_stack) && is_array($proj->tech_stack)) {
            foreach ($proj->tech_stack as $stack) {
                $s = trim($stack);
                if ($s) {
                    $stacksCount[$s] = ($stacksCount[$s] ?? 0) + 1;
                }
            }
        }
    }
    arsort($stacksCount);
    $topStacks = array_slice($stacksCount, 0, 8, true);

    // 3. Skills Matrix Analysis
    $skills = Skill::where('profile_id', $profile->id)->get();
    $totalSkills = $skills->count();
    $avgProficiency = $totalSkills > 0 ? (int)round($skills->avg('proficiency')) : 0;

    $categories = [
        'Frontend' => ['count' => 0, 'avg' => 0, 'skills' => []],
        'Backend' => ['count' => 0, 'avg' => 0, 'skills' => []],
        'DevOps' => ['count' => 0, 'avg' => 0, 'skills' => []],
        'Database' => ['count' => 0, 'avg' => 0, 'skills' => []],
        'AI/ML' => ['count' => 0, 'avg' => 0, 'skills' => []],
        'Mobile' => ['count' => 0, 'avg' => 0, 'skills' => []],
    ];

    foreach ($skills as $skill) {
        $cat = $skill->category ?: 'Backend';
        if (!isset($categories[$cat])) {
            $categories[$cat] = ['count' => 0, 'avg' => 0, 'skills' => []];
        }
        $categories[$cat]['count']++;
        $categories[$cat]['skills'][] = $skill;
    }

    foreach ($categories as $catName => &$catData) {
        if ($catData['count'] > 0) {
            $profSum = array_sum(array_map(fn($s) => $s->proficiency ?? 80, $catData['skills']));
            $catData['avg'] = (int)round($profSum / $catData['count']);
        }
    }
    unset($catData);

    $topMasterySkills = $skills->sortByDesc('proficiency')->take(6);

    // 4. AI Career Generation Telemetry
    $planSlug = $account?->plan_slug ?: 'free';
    $monthlyLimit = config("plans.{$planSlug}.ai_resumes_monthly_limit", 3);
    $usedThisMonth = $account?->ai_generations_used_current_period ?? 0;

    $totalResumes = ResumeGeneration::where('profile_id', $profile->id)->count();
    $totalCoverLetters = CoverLetterGeneration::where('profile_id', $profile->id)->count();
    $hasByok = $account ? AiSetting::where('account_id', $account->id)->where('is_active', true)->whereNotNull('api_key')->exists() : false;

    // 5. Job Pipeline Funnel
    $jobApps = JobApplication::where('profile_id', $profile->id)->get();
    $totalApps = $jobApps->count();
    $stageCounts = [
        'saved' => $jobApps->where('status', 'saved')->count(),
        'applied' => $jobApps->where('status', 'applied')->count(),
        'interviewing' => $jobApps->where('status', 'interviewing')->count(),
        'offered' => $jobApps->where('status', 'offered')->count(),
        'rejected' => $jobApps->where('status', 'rejected')->count(),
    ];

    $activeCandidates = $stageCounts['applied'] + $stageCounts['interviewing'] + $stageCounts['offered'];
    $interviewRate = ($stageCounts['applied'] + $stageCounts['interviewing'] + $stageCounts['offered'] > 0)
        ? (int)round((($stageCounts['interviewing'] + $stageCounts['offered']) / max(1, $stageCounts['applied'] + $stageCounts['interviewing'] + $stageCounts['offered'])) * 100)
        : 0;

    $offerRate = ($stageCounts['interviewing'] + $stageCounts['offered'] > 0)
        ? (int)round(($stageCounts['offered'] / max(1, $stageCounts['interviewing'] + $stageCounts['offered'])) * 100)
        : 0;

    $topCompanies = $jobApps->groupBy('company')->map->count()->sortDesc()->take(5);

    // 6. Custom Domain & Verification
    $hasVerifiedDomain = $profile->domains()->whereNotNull('verified_at')->exists();
    $customDomain = $profile->domains()->first();

    // 7. Overall Health Score Calculation
    $healthScore = (int)round(
        ($completenessScore * 0.35) +
        (min(100, $totalProjects * 25) * 0.20) +
        (min(100, $totalSkills * 10) * 0.15) +
        (min(100, ($totalResumes + $totalCoverLetters) * 20) * 0.15) +
        ($hasVerifiedDomain ? 15 : ($profile->is_published ? 10 : 0))
    );
    $healthScore = min(100, max(5, $healthScore));

    // 8. Actionable Recommendations
    $recs = [];
    if (!$profile->is_published) {
        $recs[] = [
            'type' => 'critical',
            'title' => 'Publish Your Live Portfolio',
            'desc' => 'Your profile is currently in draft mode and inaccessible to recruiters.',
            'action_label' => 'Publish Now',
            'route' => 'developer.profile',
            'icon' => 'globe',
        ];
    }
    if ($totalProjects < 3) {
        $recs[] = [
            'type' => 'important',
            'title' => 'Showcase at Least 3 Projects',
            'desc' => 'Portfolios with 3+ live demo links see 4.2x higher recruiter response times.',
            'action_label' => 'Add Project',
            'route' => 'developer.projects',
            'icon' => 'folder',
        ];
    }
    if ($totalSkills < 6) {
        $recs[] = [
            'type' => 'growth',
            'title' => 'Expand Your Skills Matrix',
            'desc' => 'Tag core competencies across Frontend, Backend, and DevOps to match job postings.',
            'action_label' => 'Add Skills',
            'route' => 'developer.skills',
            'icon' => 'sparkles',
        ];
    }
    if (!$hasVerifiedDomain && $planSlug !== 'free') {
        $recs[] = [
            'type' => 'branding',
            'title' => 'Connect a Branded Custom Apex Domain',
            'desc' => 'Elevate your personal engineering brand with your own .dev or .com domain.',
            'action_label' => 'Setup Domain',
            'route' => 'developer.domains',
            'icon' => 'link',
        ];
    }
    if ($totalResumes === 0) {
        $recs[] = [
            'type' => 'ai',
            'title' => 'Generate Your First AI-Tailored Resume',
            'desc' => 'Upload a target job description to generate an ATS-optimized PDF resume.',
            'action_label' => 'Tailor Resume',
            'route' => 'developer.resumes',
            'icon' => 'document-text',
        ];
    }
    if (!$hasByok && $planSlug === 'free') {
        $recs[] = [
            'type' => 'power',
            'title' => 'Enable BYOK for Unlimited AI Generation',
            'desc' => 'Plug in your OpenAI or Anthropic API key to bypass monthly resume generation limits.',
            'action_label' => 'Configure Keys',
            'route' => 'developer.ai-settings',
            'icon' => 'key',
        ];
    }

    return [
        'hasProfile' => true,
        'profile' => $profile,
        'account' => $account,
        'healthScore' => $healthScore,
        'completeness' => $completenessScore,
        'checklist' => $completenessItems,
        'projects' => [
            'total' => $totalProjects,
            'featured' => $featuredProjects,
            'withLiveUrl' => $liveProjects,
            'withRepoUrl' => $repoProjects,
            'topStacks' => $topStacks,
        ],
        'skills' => [
            'total' => $totalSkills,
            'categories' => $categories,
            'topSkills' => $topMasterySkills,
            'avgProficiency' => $avgProficiency,
        ],
        'career' => [
            'resumes' => $totalResumes,
            'coverLetters' => $totalCoverLetters,
            'quotaLimit' => $monthlyLimit,
            'quotaUsed' => $usedThisMonth,
            'hasByok' => $hasByok,
            'planSlug' => $planSlug,
        ],
        'jobFunnel' => [
            'total' => $totalApps,
            'stages' => $stageCounts,
            'interviewRate' => $interviewRate,
            'offerRate' => $offerRate,
            'topCompanies' => $topCompanies,
        ],
        'seo' => [
            'isPublished' => (bool)$profile->is_published,
            'isDiscoverable' => (bool)$profile->is_discoverable,
            'hasCustomDomain' => $hasVerifiedDomain,
            'customDomainName' => $customDomain?->domain,
            'hasMetaDesc' => !empty($profile->meta_description),
        ],
        'recommendations' => $recs,
    ];
});

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    {{-- Header with Engineering Intelligence Badge & Overall Health Score --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    TELEMETRY & INTELLIGENCE
                </span>
                <span class="text-xs text-slate-500 font-mono">LIVE RADAR</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Developer Operations & Analytics Center
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Real-time telemetry, portfolio health score, skills competency distribution, and job application funnel analytics.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('developer.profile') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-emerald-400 hover:border-emerald-500/40 text-xs font-semibold transition-all flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                <span>Edit Portfolio</span>
            </a>
            @if($this->analytics['seo']['isPublished'] && $this->analytics['hasProfile'])
                <a href="{{ url('/' . $this->analytics['profile']->slug) }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all flex items-center gap-2 shadow-lg shadow-emerald-950/40">
                    <span class="w-2 h-2 rounded-full bg-slate-950 animate-pulse"></span>
                    <span>View Live Site</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            @endif
        </div>
    </div>

    {{-- PRIMARY KPI RIBBON --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Health Score Card --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">ENGINEERING HEALTH</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->analytics['healthScore'] }}</span>
                <span class="text-xs text-slate-500 font-mono">/ 100</span>
            </div>
            <div class="mt-3 w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full transition-all duration-500" style="width: {{ $this->analytics['healthScore'] }}%"></div>
            </div>
            <div class="mt-2 text-[11px] text-slate-400 flex justify-between">
                <span>Profile Completeness</span>
                <span class="font-mono text-emerald-400 font-bold">{{ $this->analytics['completeness'] }}%</span>
            </div>
        </div>

        {{-- Assets Showcase Velocity --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">PORTFOLIO ASSETS</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->analytics['projects']['total'] }}</span>
                <span class="text-xs text-slate-400">Projects ({{ $this->analytics['projects']['featured'] }} Featured)</span>
            </div>
            <div class="mt-3 flex items-center gap-3 text-[11px] text-slate-400">
                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> {{ $this->analytics['skills']['total'] }} Skills</span>
                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> {{ $this->analytics['projects']['withLiveUrl'] }} Demos</span>
            </div>
        </div>

        {{-- AI Acceleration Suite --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">AI CAREER SUITE</span>
                <div class="w-8 h-8 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->analytics['career']['resumes'] + $this->analytics['career']['coverLetters'] }}</span>
                <span class="text-xs text-slate-400">AI Tailored Docs</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Quota (Month): <strong class="text-white">{{ $this->analytics['career']['quotaUsed'] }}/{{ $this->analytics['career']['hasByok'] ? '∞' : $this->analytics['career']['quotaLimit'] }}</strong></span>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono {{ $this->analytics['career']['hasByok'] ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">
                    {{ $this->analytics['career']['hasByok'] ? 'BYOK UNLIMITED' : strtoupper($this->analytics['career']['planSlug']) }}
                </span>
            </div>
        </div>

        {{-- Job Conversion Funnel --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">INTERVIEW CONVERSION</span>
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->analytics['jobFunnel']['interviewRate'] }}%</span>
                <span class="text-xs text-slate-400">Interview Rate</span>
            </div>
            <div class="mt-3 flex items-center gap-3 text-[11px] text-slate-400">
                <span>{{ $this->analytics['jobFunnel']['total'] }} Tracked</span>
                <span>&bull;</span>
                <span class="text-emerald-400">{{ $this->analytics['jobFunnel']['stages']['offered'] ?? 0 }} Offers</span>
            </div>
        </div>
    </div>

    {{-- ACTIONABLE RECOMMENDATIONS & INTELLIGENCE RADAR --}}
    @if(count($this->analytics['recommendations']) > 0)
        <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 bg-slate-900/40">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></div>
                    <h3 class="text-sm font-bold font-heading text-white uppercase tracking-wider">
                        Optimization Radar ({{ count($this->analytics['recommendations']) }} High-Impact Actions)
                    </h3>
                </div>
                <span class="text-xs text-slate-400">Auto-calculated from portfolio gap analysis</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->analytics['recommendations'] as $rec)
                    <div class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 hover:border-emerald-500/30 transition-all flex flex-col justify-between space-y-3 group">
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold
                                    {{ $rec['type'] === 'critical' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : '' }}
                                    {{ $rec['type'] === 'important' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                                    {{ $rec['type'] === 'growth' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : '' }}
                                    {{ $rec['type'] === 'ai' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : '' }}
                                    {{ $rec['type'] === 'branding' || $rec['type'] === 'power' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                                ">
                                    {{ strtoupper($rec['type']) }}
                                </span>
                            </div>
                            <h4 class="text-xs font-bold text-white group-hover:text-emerald-400 transition-colors">
                                {{ $rec['title'] }}
                            </h4>
                            <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                                {{ $rec['desc'] }}
                            </p>
                        </div>
                        <a href="{{ route($rec['route']) }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1 transition-colors pt-2 border-t border-white/5">
                            <span>{{ $rec['action_label'] }}</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- DETAILED ANALYTICAL PILLARS (2-COLUMN GRID) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- PILLAR 1: Skills Matrix & Technical Competency Breakdown --}}
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold font-heading text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Technical Skills & Competency Matrix</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Distribution and proficiency across engineering disciplines.</p>
                </div>
                <a href="{{ route('developer.skills') }}" class="text-xs text-emerald-400 hover:underline">Manage Skills</a>
            </div>

            {{-- Category Breakdown Bars --}}
            <div class="space-y-4">
                @foreach($this->analytics['skills']['categories'] as $catName => $catData)
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-xs">
                            <span class="font-medium text-slate-300">{{ $catName }} ({{ $catData['count'] }} skills)</span>
                            <span class="font-mono text-slate-400">{{ $catData['avg'] }}% Mastery</span>
                        </div>
                        <div class="w-full bg-slate-900 border border-slate-800 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-500 to-cyan-400 h-full rounded-full transition-all duration-500" style="width: {{ $catData['avg'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Top Mastery Badges --}}
            @if(count($this->analytics['skills']['topSkills']) > 0)
                <div class="pt-4 border-t border-white/5">
                    <span class="text-[11px] font-mono uppercase text-slate-400 font-semibold block mb-3">Top Mastery Competencies</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->analytics['skills']['topSkills'] as $topSkill)
                            <span class="px-3 py-1 rounded-xl bg-slate-900 border border-emerald-500/20 text-slate-200 text-xs flex items-center gap-2 font-mono">
                                <span class="text-emerald-400 font-bold">{{ $topSkill->proficiency }}%</span>
                                <span>{{ $topSkill->name }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- PILLAR 2: Job Application Funnel Conversion Pipeline --}}
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold font-heading text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                        <span>Job Application Pipeline & Funnel</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Conversion stages across active job search campaigns.</p>
                </div>
                <a href="{{ route('developer.job-tracker') }}" class="text-xs text-purple-400 hover:underline">Open Kanban</a>
            </div>

            {{-- 5-Stage Funnel Flow --}}
            <div class="grid grid-cols-5 gap-2 text-center">
                <div class="p-3 rounded-2xl bg-slate-900/80 border border-slate-800">
                    <div class="text-[10px] font-mono text-slate-400 uppercase">Saved</div>
                    <div class="text-xl font-bold font-heading text-slate-200 mt-1">{{ $this->analytics['jobFunnel']['stages']['saved'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-900/80 border border-blue-500/20">
                    <div class="text-[10px] font-mono text-blue-400 uppercase">Applied</div>
                    <div class="text-xl font-bold font-heading text-blue-300 mt-1">{{ $this->analytics['jobFunnel']['stages']['applied'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-900/80 border border-amber-500/20">
                    <div class="text-[10px] font-mono text-amber-400 uppercase">Interview</div>
                    <div class="text-xl font-bold font-heading text-amber-300 mt-1">{{ $this->analytics['jobFunnel']['stages']['interviewing'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-900/80 border border-emerald-500/30 bg-emerald-500/5">
                    <div class="text-[10px] font-mono text-emerald-400 uppercase font-bold">Offer</div>
                    <div class="text-xl font-bold font-heading text-emerald-300 mt-1">{{ $this->analytics['jobFunnel']['stages']['offered'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-900/80 border border-rose-500/20">
                    <div class="text-[10px] font-mono text-rose-400 uppercase">Closed</div>
                    <div class="text-xl font-bold font-heading text-rose-300 mt-1">{{ $this->analytics['jobFunnel']['stages']['rejected'] ?? 0 }}</div>
                </div>
            </div>

            {{-- Target Companies & Conversion KPI --}}
            <div class="pt-4 border-t border-white/5 grid grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/5">
                    <span class="text-[10px] font-mono text-slate-400 uppercase block mb-1">Interview Conversion Rate</span>
                    <div class="text-2xl font-extrabold text-purple-400 font-heading">{{ $this->analytics['jobFunnel']['interviewRate'] }}%</div>
                    <span class="text-[10px] text-slate-500 mt-1 block">From applied to first round</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/5">
                    <span class="text-[10px] font-mono text-slate-400 uppercase block mb-1">Offer Conversion Rate</span>
                    <div class="text-2xl font-extrabold text-emerald-400 font-heading">{{ $this->analytics['jobFunnel']['offerRate'] }}%</div>
                    <span class="text-[10px] text-slate-500 mt-1 block">From interview to final offer</span>
                </div>
            </div>
        </div>
    </div>

    {{-- PILLAR 3 & 4: Projects Showcase Radar & SEO/Platform Telemetry --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Projects Tech Stack Frequency --}}
        <div class="lg:col-span-2 glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold font-heading text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        <span>Projects Showcase & Tech Stack Fingerprint</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Breakdown of technology stacks used across your featured work.</p>
                </div>
                <a href="{{ route('developer.projects') }}" class="text-xs text-cyan-400 hover:underline">View Projects</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 text-center">
                    <div class="text-xs text-slate-400">Total Showcases</div>
                    <div class="text-2xl font-bold text-white font-heading mt-1">{{ $this->analytics['projects']['total'] }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 text-center">
                    <div class="text-xs text-slate-400">Featured Work</div>
                    <div class="text-2xl font-bold text-emerald-400 font-heading mt-1">{{ $this->analytics['projects']['featured'] }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 text-center">
                    <div class="text-xs text-slate-400">Live Deployments</div>
                    <div class="text-2xl font-bold text-cyan-400 font-heading mt-1">{{ $this->analytics['projects']['withLiveUrl'] }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 text-center">
                    <div class="text-xs text-slate-400">Code Repositories</div>
                    <div class="text-2xl font-bold text-purple-400 font-heading mt-1">{{ $this->analytics['projects']['withRepoUrl'] }}</div>
                </div>
            </div>

            {{-- Tech Stack Tags --}}
            @if(count($this->analytics['projects']['topStacks']) > 0)
                <div class="pt-4 border-t border-white/5">
                    <span class="text-[11px] font-mono uppercase text-slate-400 font-semibold block mb-3">Dominant Technologies in Showcase</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->analytics['projects']['topStacks'] as $stackName => $stackFreq)
                            <span class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 flex items-center gap-2">
                                <span class="font-bold text-white">{{ $stackName }}</span>
                                <span class="px-1.5 py-0.5 rounded-full bg-slate-800 text-[10px] text-cyan-400 font-mono">{{ $stackFreq }} {{ $stackFreq === 1 ? 'project' : 'projects' }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- SEO, Domain & Platform Health --}}
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div>
                <h3 class="text-lg font-bold font-heading text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                    <span>SEO & Domain Audit</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">Platform discoverability and custom branding audit.</p>
            </div>

            <div class="space-y-3">
                <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full {{ $this->analytics['seo']['isPublished'] ? 'bg-emerald-400' : 'bg-rose-400' }}"></div>
                        <span class="text-xs text-slate-200">Public Portfolio</span>
                    </div>
                    <span class="text-xs font-mono {{ $this->analytics['seo']['isPublished'] ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ $this->analytics['seo']['isPublished'] ? 'LIVE' : 'DRAFT' }}
                    </span>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full {{ $this->analytics['seo']['isDiscoverable'] ? 'bg-emerald-400' : 'bg-slate-500' }}"></div>
                        <span class="text-xs text-slate-200">Talent Directory (/discover)</span>
                    </div>
                    <span class="text-xs font-mono {{ $this->analytics['seo']['isDiscoverable'] ? 'text-emerald-400' : 'text-slate-400' }}">
                        {{ $this->analytics['seo']['isDiscoverable'] ? 'INDEXED' : 'HIDDEN' }}
                    </span>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full {{ $this->analytics['seo']['hasCustomDomain'] ? 'bg-emerald-400' : 'bg-amber-400' }}"></div>
                        <span class="text-xs text-slate-200">Custom Domain</span>
                    </div>
                    <span class="text-xs font-mono {{ $this->analytics['seo']['hasCustomDomain'] ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ $this->analytics['seo']['hasCustomDomain'] ? $this->analytics['seo']['customDomainName'] : 'SYSTEM SLUG' }}
                    </span>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full {{ $this->analytics['seo']['hasMetaDesc'] ? 'bg-emerald-400' : 'bg-slate-500' }}"></div>
                        <span class="text-xs text-slate-200">OpenGraph & Meta Tags</span>
                    </div>
                    <span class="text-xs font-mono {{ $this->analytics['seo']['hasMetaDesc'] ? 'text-emerald-400' : 'text-slate-400' }}">
                        {{ $this->analytics['seo']['hasMetaDesc'] ? 'OPTIMIZED' : 'DEFAULT' }}
                    </span>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('developer.domains') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-200 text-xs font-semibold flex items-center justify-center gap-2 transition-all">
                    <span>Manage Custom Domains & DNS</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    </div>
</div>

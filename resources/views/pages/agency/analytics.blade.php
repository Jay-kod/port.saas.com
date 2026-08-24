<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\Profile;
use App\Models\Account;
use App\Models\Project;
use App\Models\Skill;
use App\Models\ResumeGeneration;
use App\Models\CoverLetterGeneration;
use App\Models\Domain;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Agency Operations & Analytics');

$account = computed(function () {
    $user = Auth::user();
    return (session('active_tenant_id') ? Account::find(session('active_tenant_id')) : null)
        ?? $user?->accounts()->first()
        ?? $user?->memberAccounts()->first();
});

$analytics = computed(function () {
    $account = $this->account;
    if (! $account) {
        return [
            'totalClients' => 0,
            'publishedClients' => 0,
            'totalProjects' => 0,
            'totalSkills' => 0,
            'totalAiDocs' => 0,
            'avgCompleteness' => 0,
            'clientLeaderboard' => [],
            'topTechStacks' => [],
        ];
    }

    $profiles = $account->profiles()->get();
    $totalClients = $profiles->count();
    $publishedClients = $profiles->where('is_published', true)->count();
    
    $profileIds = $profiles->pluck('id');
    $totalProjects = Project::whereIn('profile_id', $profileIds)->count();
    $totalSkills = Skill::whereIn('profile_id', $profileIds)->count();
    $totalResumes = ResumeGeneration::whereIn('profile_id', $profileIds)->count();
    $totalCoverLetters = CoverLetterGeneration::whereIn('profile_id', $profileIds)->count();
    $totalAiDocs = $totalResumes + $totalCoverLetters;

    // Build client health leaderboard
    $leaderboard = [];
    $completenessScores = [];
    $allTechStacks = [];

    foreach ($profiles as $p) {
        $projCount = $p->projects()->count();
        $skillCount = $p->skills()->count();
        $hasCustomDomain = $p->domains()->whereNotNull('verified_at')->exists();
        
        // Completeness score
        $score = 0;
        if (!empty($p->full_name)) $score += 15;
        if (!empty($p->headline)) $score += 15;
        if (!empty($p->bio)) $score += 20;
        if ($projCount >= 3) $score += 20; elseif ($projCount > 0) $score += 10;
        if ($skillCount >= 5) $score += 15; elseif ($skillCount > 0) $score += 8;
        if ($p->is_published) $score += 15;
        $score = min(100, $score);
        $completenessScores[] = $score;

        // Gather stacks
        foreach ($p->projects as $pr) {
            if (is_array($pr->tech_stack)) {
                foreach ($pr->tech_stack as $st) {
                    $s = trim($st);
                    if ($s) {
                        $allTechStacks[$s] = ($allTechStacks[$s] ?? 0) + 1;
                    }
                }
            }
        }

        $leaderboard[] = [
            'id' => $p->id,
            'name' => $p->full_name ?: 'Unnamed Profile',
            'headline' => $p->headline ?: 'Software Engineer',
            'slug' => $p->slug,
            'is_published' => (bool)$p->is_published,
            'is_discoverable' => (bool)$p->is_discoverable,
            'has_domain' => $hasCustomDomain,
            'projects_count' => $projCount,
            'skills_count' => $skillCount,
            'health_score' => $score,
        ];
    }

    usort($leaderboard, fn($a, $b) => $b['health_score'] <=> $a['health_score']);
    arsort($allTechStacks);
    $topStacks = array_slice($allTechStacks, 0, 10, true);

    $avgCompleteness = count($completenessScores) > 0 
        ? (int)round(array_sum($completenessScores) / count($completenessScores)) 
        : 0;

    return [
        'totalClients' => $totalClients,
        'publishedClients' => $publishedClients,
        'totalProjects' => $totalProjects,
        'totalSkills' => $totalSkills,
        'totalAiDocs' => $totalAiDocs,
        'avgCompleteness' => $avgCompleteness,
        'clientLeaderboard' => $leaderboard,
        'topTechStacks' => $topStacks,
    ];
});

$switchActiveClient = function ($profileId) {
    $profile = Profile::where('account_id', $this->account?->id)->findOrFail($profileId);
    session(['active_profile_id' => $profile->id]);
    return redirect()->route('developer.profile');
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20">
                    AGENCY INTELLIGENCE
                </span>
                <span class="text-xs text-slate-500 font-mono">MULTI-CLIENT RADAR</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Agency Operations & Analytics Center
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Aggregated multi-client portfolio health scores, tech stack competencies, and live deployment telemetry.
            </p>
        </div>
    </div>

    {{-- PRIMARY KPI RIBBON --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Managed Clients --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">MANAGED CLIENTS</span>
                <div class="w-8 h-8 rounded-xl bg-teal-500/10 border border-teal-500/20 text-teal-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->analytics['totalClients'] }}</span>
                <span class="text-xs text-teal-400 font-mono">({{ $this->analytics['publishedClients'] }} Live)</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400">
                Live Published Ratio: <strong class="text-white">{{ $this->analytics['totalClients'] > 0 ? (int)round(($this->analytics['publishedClients'] / $this->analytics['totalClients']) * 100) : 0 }}%</strong>
            </div>
        </div>

        {{-- Average Health Score --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">PORTFOLIO HEALTH</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->analytics['avgCompleteness'] }}</span>
                <span class="text-xs text-slate-500 font-mono">/ 100</span>
            </div>
            <div class="mt-3 w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div class="bg-gradient-to-r from-teal-500 to-cyan-400 h-full rounded-full transition-all duration-500" style="width: {{ $this->analytics['avgCompleteness'] }}%"></div>
            </div>
        </div>

        {{-- Client Showcase Assets --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">CLIENT SHOWCASES</span>
                <div class="w-8 h-8 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->analytics['totalProjects'] }}</span>
                <span class="text-xs text-slate-400">Projects</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400">
                Tagged Competencies: <strong class="text-white">{{ $this->analytics['totalSkills'] }} Skills</strong>
            </div>
        </div>

        {{-- Aggregate AI Career Generations --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">AI CAREER ASSETS</span>
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->analytics['totalAiDocs'] }}</span>
                <span class="text-xs text-slate-400">AI Resumes Tailored</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400">
                Across all managed client portfolios
            </div>
        </div>
    </div>

    {{-- MULTI-CLIENT PORTFOLIO HEALTH LEADERBOARD --}}
    <div class="glass-card rounded-3xl overflow-hidden border border-white/10">
        <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold font-heading text-white">Client Portfolio Health Leaderboard</h3>
                <p class="text-xs text-slate-400">Ranked by completeness, asset count, and publishing readiness.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">{{ count($this->analytics['clientLeaderboard']) }} Profiles Audited</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-900/80 border-b border-white/5 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Rank / Client</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Health Score</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Status</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Showcases</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Talent Directory</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px] text-right">Quick Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($this->analytics['clientLeaderboard'] as $index => $client)
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-white">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono text-slate-500 text-xs w-4">#{{ $index + 1 }}</span>
                                    <div class="w-8 h-8 rounded-xl bg-teal-500/10 border border-teal-500/20 text-teal-300 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($client['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $client['name'] }}</div>
                                        <div class="text-[10px] text-slate-400 truncate max-w-[200px]">{{ $client['headline'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-gradient-to-r from-teal-500 to-cyan-400 h-full rounded-full" style="width: {{ $client['health_score'] }}%"></div>
                                    </div>
                                    <span class="font-mono font-bold text-teal-400">{{ $client['health_score'] }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($client['is_published'])
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20">LIVE</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-800 text-slate-400 border border-slate-700">DRAFT</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-300">
                                {{ $client['projects_count'] }} Proj &bull; {{ $client['skills_count'] }} Skills
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-mono {{ $client['is_discoverable'] ? 'text-teal-400' : 'text-slate-500' }}">
                                    {{ $client['is_discoverable'] ? 'INDEXED' : 'HIDDEN' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <button type="button" wire:click="switchActiveClient({{ $client['id'] }})" class="text-teal-400 hover:text-teal-300 font-semibold cursor-pointer">
                                    Open Studio &rarr;
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500 italic">
                                No client portfolios available for audit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TECH STACK RADAR ACROSS ALL CLIENTS --}}
    @if(count($this->analytics['topTechStacks']) > 0)
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
            <h3 class="text-lg font-bold font-heading text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                <span>Agency Tech Competency Radar</span>
            </h3>
            <p class="text-xs text-slate-400">Aggregated framework and programming language frequency across all client project showcases.</p>

            <div class="flex flex-wrap gap-2.5 pt-2">
                @foreach($this->analytics['topTechStacks'] as $techName => $techCount)
                    <div class="px-3.5 py-2 rounded-xl bg-slate-950/80 border border-white/5 flex items-center gap-2">
                        <span class="text-white font-bold text-xs">{{ $techName }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-teal-500/10 text-teal-300 border border-teal-500/20 text-[10px] font-mono">
                            {{ $techCount }} {{ $techCount === 1 ? 'project' : 'projects' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

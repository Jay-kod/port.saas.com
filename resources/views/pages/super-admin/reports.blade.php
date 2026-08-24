<?php

use function Livewire\Volt\{state, layout, title, usesPagination, computed};
use App\Models\PortfolioReport;
use App\Models\Profile;

layout('layouts.super-admin');
title('Abuse Moderation Queue');

usesPagination();

state([
    'statusFilter' => 'all', // all, pending, reviewed, resolved, dismissed
    'reasonFilter' => 'all', // all, spam, harassment, copyright, malicious_links, inappropriate
    'showDetailModal' => false,
    'selectedReport' => null,
    'successMessage' => '',
    'errorMessage' => '',
]);

$reports = computed(function () {
    return PortfolioReport::query()
        ->with(['profile.account'])
        ->when($this->statusFilter !== 'all', function ($query) {
            $query->where('status', $this->statusFilter);
        })
        ->when($this->reasonFilter !== 'all', function ($query) {
            $query->where('reason', $this->reasonFilter);
        })
        ->latest()
        ->paginate(12);
});

$openDetail = function ($reportId) {
    $this->selectedReport = PortfolioReport::with(['profile.account'])->findOrFail($reportId);
    $this->showDetailModal = true;
};

$resolveReport = function ($reportId, $status) {
    $report = PortfolioReport::findOrFail($reportId);
    $report->status = $status;
    $report->save();

    $this->successMessage = "Report #{$report->id} has been marked as [{$status}].";
    if ($this->selectedReport && $this->selectedReport->id === $report->id) {
        $this->selectedReport = $report->fresh();
    }
};

$suspendAndUnpublish = function ($reportId) {
    $report = PortfolioReport::with('profile')->findOrFail($reportId);
    
    if ($report->profile) {
        $report->profile->is_published = false;
        $report->profile->is_discoverable = false;
        $report->profile->save();
    }

    $report->status = 'resolved';
    $report->save();

    $this->successMessage = "Target portfolio #{$report->profile_id} has been unpublished & suspended. Report marked resolved.";
    $this->showDetailModal = false;
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-red-500/20 text-red-300 border border-red-500/30 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span>
                    SAFETY & COMPLIANCE
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-900 text-slate-400 border border-slate-800">
                    INCIDENT QUEUE
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Portfolio Moderation & Abuse Reports
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Review flagged user portfolios, investigate spam and abuse violations, and take down malicious content.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition-all cursor-pointer" data-tooltip="Return to Super Admin Master Control Hub">
                &larr; Telemetry Hub
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($successMessage)
    <div class="p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-200 text-xs sm:text-sm flex items-center justify-between font-mono animate-fadeIn">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span>{{ $successMessage }}</span>
        </div>
        <button wire:click="$set('successMessage', '')" class="text-amber-400 hover:text-white underline text-xs cursor-pointer" data-tooltip="Dismiss notification">Dismiss</button>
    </div>
    @endif

    <!-- Toolbar: Status & Category Filters -->
    <div class="glass-card-dark rounded-3xl p-5 border border-amber-950/70 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Status Filter -->
            <div class="flex items-center gap-1.5 font-mono text-xs overflow-x-auto pb-1 sm:pb-0">
                <button type="button" 
                        wire:click="$set('statusFilter', 'all')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $statusFilter === 'all' ? 'bg-amber-600 text-slate-950 shadow-md shadow-amber-950' : 'bg-slate-900 text-slate-400 hover:text-white' }}"
                        data-tooltip="View all abuse reports">
                    All Reports
                </button>
                <button type="button" 
                        wire:click="$set('statusFilter', 'pending')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $statusFilter === 'pending' ? 'bg-red-600 text-white shadow-md shadow-red-950' : 'bg-slate-900 text-slate-400 hover:text-white' }}"
                        data-tooltip="Filter by pending open reports">
                    Pending
                </button>
                <button type="button" 
                        wire:click="$set('statusFilter', 'resolved')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $statusFilter === 'resolved' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-950' : 'bg-slate-900 text-slate-400 hover:text-white' }}"
                        data-tooltip="Filter by resolved reports">
                    Resolved
                </button>
                <button type="button" 
                        wire:click="$set('statusFilter', 'dismissed')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $statusFilter === 'dismissed' ? 'bg-slate-800 text-slate-300 border border-slate-700' : 'bg-slate-900 text-slate-400 hover:text-white' }}"
                        data-tooltip="Filter by dismissed false positives">
                    Dismissed
                </button>
            </div>

            <!-- Category Selector -->
            <div class="flex items-center gap-2 font-mono text-xs">
                <span class="text-slate-500">Reason:</span>
                <select wire:model.live="reasonFilter" class="px-3 py-1.5 rounded-xl bg-black border border-amber-950 text-white focus:outline-none focus:border-amber-500 text-xs cursor-pointer" data-tooltip="Filter reports by violation category">
                    <option value="all">All Violation Types</option>
                    <option value="spam">Commercial Spam</option>
                    <option value="harassment">Harassment / Abusive</option>
                    <option value="copyright">Copyright Infringement</option>
                    <option value="malicious_links">Phishing / Malicious Links</option>
                    <option value="inappropriate">Inappropriate Content</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="glass-card-dark rounded-3xl overflow-hidden border border-amber-950/70 shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs font-mono">
                <thead class="bg-black/95 border-b border-amber-950 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Report ID & Target</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Reason & Summary</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Reporter IP</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Status</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Reported</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-950/40">
                    @forelse($this->reports as $report)
                    <tr class="hover:bg-amber-950/15 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-white text-sm">Report #{{ $report->id }}</div>
                            @if($report->profile)
                            <a href="{{ url('/' . $report->profile->slug) }}" target="_blank" class="text-amber-400 hover:underline flex items-center gap-1 text-[11px] cursor-pointer" data-tooltip="Open reported portfolio site in a new tab">
                                <span>/{{ $report->profile->slug }}</span>
                                <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                            @else
                            <span class="text-slate-500">Deleted Profile #{{ $report->profile_id }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-300 max-w-xs">
                            <div class="font-bold text-white uppercase text-[10px]">{{ $report->reason }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ $report->details ?: 'No details provided' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-400 font-mono text-[10px]">
                            {{ $report->reporter_ip ?: 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $report->status === 'pending' ? 'bg-red-500/20 text-red-300 border border-red-500/40 animate-pulse' : ($report->status === 'resolved' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-slate-800 text-slate-400') }}">
                                {{ $report->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-[10px]">
                            {{ $report->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            <button type="button" 
                                    wire:click="openDetail({{ $report->id }})" 
                                    class="px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 text-[10px] font-bold transition-all cursor-pointer"
                                    data-tooltip="Open incident investigation and evidence panel">
                                Inspect
                            </button>
                            @if($report->status === 'pending')
                            <button type="button" 
                                    wire:click="resolveReport({{ $report->id }}, 'resolved')" 
                                    class="px-2.5 py-1 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-[10px] font-bold transition-all cursor-pointer"
                                    data-tooltip="Mark report resolved and close investigation">
                                Resolve
                            </button>
                            <button type="button" 
                                    wire:click="resolveReport({{ $report->id }}, 'dismissed')" 
                                    class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white border border-slate-800 text-[10px] transition-all cursor-pointer"
                                    data-tooltip="Dismiss abuse report as false positive">
                                Dismiss
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">No abuse reports found in moderation queue.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-amber-950/60 bg-black/60">
            {{ $this->reports->links() }}
        </div>
    </div>

    <!-- Report Inspection Modal -->
    @if($showDetailModal && $selectedReport)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fadeIn font-mono">
        <div class="relative w-full max-w-xl p-6 rounded-3xl glass-card-dark bg-black/95 border border-amber-500/40 shadow-2xl space-y-5" @click.outside="$set('showDetailModal', false)">
            <div class="flex items-center justify-between border-b border-amber-950/60 pb-3">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-red-500/20 text-red-300 text-xs font-bold uppercase">Report #{{ $selectedReport->id }}</span>
                    <h3 class="text-base font-bold text-white">Incident Investigation</h3>
                </div>
                <button type="button" wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-white text-lg cursor-pointer" data-tooltip="Close investigation modal">&times;</button>
            </div>

            <div class="space-y-4 text-xs text-slate-300">
                <div class="p-3 rounded-xl bg-slate-950 border border-white/5 space-y-1">
                    <div class="text-[10px] text-slate-500 uppercase">Target Portfolio</div>
                    <div class="font-bold text-white text-sm">{{ $selectedReport->profile?->full_name ?? 'Deleted Profile' }}</div>
                    @if($selectedReport->profile)
                    <div class="text-amber-400 font-mono text-xs">/{{ $selectedReport->profile->slug }}</div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-slate-950 border border-white/5 space-y-1">
                        <div class="text-[10px] text-slate-500 uppercase">Violation Reason</div>
                        <div class="font-bold text-red-400 uppercase">{{ $selectedReport->reason }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950 border border-white/5 space-y-1">
                        <div class="text-[10px] text-slate-500 uppercase">Reporter IP / Date</div>
                        <div class="font-bold text-slate-300">{{ $selectedReport->reporter_ip ?: '127.0.0.1' }}</div>
                        <div class="text-[10px] text-slate-500">{{ $selectedReport->created_at->toDateTimeString() }}</div>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-white/5 space-y-1">
                    <div class="text-[10px] text-slate-500 uppercase">Reporter Description & Evidence</div>
                    <p class="text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $selectedReport->details ?: 'No detailed explanation provided by reporter.' }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-3 border-t border-amber-950/60">
                <button type="button" 
                        wire:click="suspendAndUnpublish({{ $selectedReport->id }})" 
                        class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-lg shadow-red-950 transition-all cursor-pointer"
                        data-tooltip="Take down and unpublish reported portfolio immediately">
                    Suspend & Unpublish Portfolio
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" 
                            wire:click="resolveReport({{ $selectedReport->id }}, 'dismissed')" 
                            class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white text-xs font-semibold cursor-pointer"
                            data-tooltip="Dismiss report without taking enforcement action">
                        Dismiss
                    </button>
                    <button type="button" 
                            wire:click="resolveReport({{ $selectedReport->id }}, 'resolved')" 
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-950 cursor-pointer"
                            data-tooltip="Mark investigation as resolved">
                        Mark Resolved
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

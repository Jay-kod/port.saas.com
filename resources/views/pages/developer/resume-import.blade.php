<?php

use function Livewire\Volt\{state, layout, title, usesFileUploads};
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use App\Services\ResumeParserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

usesFileUploads();
layout('layouts.dashboard');
title('Import Resume PDF');

state([
    'resumeFile' => null,
    'rawText' => '',
    'step' => 1,
    'isParsing' => false,
    'parsedBio' => '',
    'parsedHeadline' => '',
    'parsedLocation' => '',
    'parsedExperiences' => [],
    'parsedSkills' => '',
    'parsedProjects' => [],
    'savedMessage' => '',
    'errorMessage' => '',
]);

$parseResume = function (ResumeParserService $parser) {
    $this->errorMessage = '';
    $this->savedMessage = '';

    $textToParse = trim($this->rawText);

    if ($this->resumeFile) {
        try {
            $filePath = $this->resumeFile->getRealPath();
            $textToParse = $parser->extractTextFromPdf($filePath);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Failed to extract text from PDF: ' . $e->getMessage();
            return;
        }
    }

    if (empty($textToParse)) {
        $this->errorMessage = 'Please upload a PDF file or paste your resume text.';
        return;
    }

    $this->isParsing = true;

    try {
        $parsed = $parser->parse($textToParse);

        $this->parsedHeadline = $parsed['headline'] ?? '';
        $this->parsedBio = $parsed['bio'] ?? '';
        $this->parsedLocation = $parsed['location'] ?? '';
        $this->parsedExperiences = $parsed['experiences'] ?? [];
        $this->parsedSkills = is_array($parsed['skills'] ?? null) ? implode(', ', $parsed['skills']) : ($parsed['skills'] ?? '');
        $this->parsedProjects = $parsed['projects'] ?? [];

        $this->step = 2;
    } catch (\Throwable $e) {
        $this->errorMessage = 'Parsing failed: ' . $e->getMessage();
    } finally {
        $this->isParsing = false;
    }
};

$importToPortfolio = function () {
    $profile = Auth::user()?->profile;
    if (! $profile) {
        $this->errorMessage = 'Profile not found.';
        return;
    }

    // 1. Update Profile Bio & Headline
    $profileUpdates = [];
    if ($this->parsedHeadline) $profileUpdates['headline'] = $this->parsedHeadline;
    if ($this->parsedBio) $profileUpdates['bio'] = $this->parsedBio;
    if ($this->parsedLocation) $profileUpdates['location'] = $this->parsedLocation;
    if (!empty($profileUpdates)) {
        $profile->update($profileUpdates);
    }

    // 2. Import Experiences
    $expCount = 0;
    foreach ($this->parsedExperiences as $exp) {
        if (!empty($exp['company']) && !empty($exp['role'])) {
            Experience::create([
                'profile_id' => $profile->id,
                'company' => $exp['company'],
                'title' => $exp['role'],
                'start_date' => !empty($exp['start_date']) ? $exp['start_date'] : now()->subYear()->toDateString(),
                'end_date' => !empty($exp['end_date']) ? $exp['end_date'] : null,
                'is_current' => empty($exp['end_date']),
                'description' => $exp['description'] ?? null,
            ]);
            $expCount++;
        }
    }

    // 3. Import Skills
    $skillCount = 0;
    $skills = array_filter(array_map('trim', explode(',', $this->parsedSkills)));
    foreach ($skills as $skillName) {
        $exists = Skill::where('profile_id', $profile->id)->where('name', $skillName)->exists();
        if (! $exists) {
            Skill::create([
                'profile_id' => $profile->id,
                'name' => $skillName,
                'category' => 'Backend',
                'proficiency' => 85,
            ]);
            $skillCount++;
        }
    }

    // 4. Import Projects
    $projCount = 0;
    foreach ($this->parsedProjects as $proj) {
        if (!empty($proj['title'])) {
            Project::create([
                'profile_id' => $profile->id,
                'title' => $proj['title'],
                'slug' => Str::slug($proj['title']),
                'summary' => $proj['description'] ?? null,
                'tech_stack' => is_array($proj['tech_stack'] ?? null) ? $proj['tech_stack'] : [],
            ]);
            $projCount++;
        }
    }

    $this->step = 1;
    $this->reset(['resumeFile', 'rawText', 'parsedExperiences', 'parsedSkills', 'parsedProjects', 'parsedBio', 'parsedHeadline', 'parsedLocation']);
    $this->savedMessage = "Successfully imported: {$expCount} experiences, {$skillCount} skills, and {$projCount} projects into your portfolio!";
};

?>

<div class="space-y-8 max-w-5xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                    CAREER & AI SUITE
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Import Resume PDF & AI Parser
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Upload your existing PDF resume to automatically populate your bio, career timeline, projects, and skills in seconds.
            </p>
        </div>

        @if($step === 2)
            <button wire:click="$set('step', 1)" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold cursor-pointer" data-tooltip="Return to document upload step">
                &larr; Re-upload File
            </button>
        @endif
    </div>

    {{-- Feedback Messages --}}
    @if($savedMessage)
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between font-mono animate-fadeIn">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $savedMessage }}</span>
            </div>
            <button wire:click="$set('savedMessage', '')" class="text-slate-400 hover:text-white cursor-pointer" data-tooltip="Dismiss notification">&times;</button>
        </div>
    @endif

    @if($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center justify-between font-mono animate-fadeIn">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', '')" class="text-slate-400 hover:text-white cursor-pointer" data-tooltip="Dismiss error notification">&times;</button>
        </div>
    @endif

    {{-- STEP 1: Upload / Input Form --}}
    @if($step === 1)
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="space-y-2">
                <h3 class="text-base font-bold font-heading text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Step 1: Upload Resume Document</span>
                </h3>
                <p class="text-xs text-slate-400">Supported format: standard PDF resume. You can also paste raw text below as fallback.</p>
            </div>

            <div class="space-y-4">
                <div class="p-6 rounded-2xl border-2 border-dashed border-slate-800 hover:border-emerald-500/50 transition-colors bg-slate-900/50 text-center space-y-3">
                    <svg class="w-8 h-8 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    <div>
                        <label class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 cursor-pointer" data-tooltip="Select PDF file from your device">
                            <span>Click to choose PDF file</span>
                            <input type="file" wire:model="resumeFile" accept=".pdf" class="hidden" />
                        </label>
                        <div class="text-[11px] text-slate-500 mt-1">PDF up to 10MB</div>
                    </div>
                    @if($resumeFile)
                        <div class="text-xs font-mono text-emerald-400 bg-slate-950 py-1.5 px-3 rounded-xl inline-block border border-emerald-500/30">
                            Selected: {{ $resumeFile->getClientOriginalName() }}
                        </div>
                    @endif
                </div>

                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-white/5"></div>
                    <span class="flex-shrink mx-4 text-xs font-mono text-slate-500 uppercase">Or Paste Plain Text</span>
                    <div class="flex-grow border-t border-white/5"></div>
                </div>

                <div class="space-y-1.5">
                    <textarea rows="6" wire:model="rawText" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="Paste your entire resume text here..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button wire:click="parseResume" wire:loading.attr="disabled" type="button" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md flex items-center gap-2 cursor-pointer" data-tooltip="Extract bio, experiences, skills, and projects using AI">
                        <span wire:loading.remove>Parse & Review Data &rarr;</span>
                        <span wire:loading>Extracting Resume Entities...</span>
                    </button>
                </div>
            </div>
        </div>

    {{-- STEP 2: Review Extracted Entities --}}
    @elseif($step === 2)
        <div class="space-y-6 animate-fadeIn">
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <div>
                        <h3 class="text-base font-bold font-heading text-white">Step 2: Review & Confirm Extracted Portfolio Data</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Verify the parsed records before saving them to your live portfolio.</p>
                    </div>
                    <button wire:click="importToPortfolio" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md cursor-pointer" data-tooltip="Commit extracted data into your portfolio">
                        Import to Portfolio &check;
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Extracted Headline</label>
                            <input type="text" wire:model="parsedHeadline" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Extracted Location</label>
                            <input type="text" wire:model="parsedLocation" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Extracted Bio</label>
                        <textarea rows="3" wire:model="parsedBio" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs"></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Extracted Skills (comma-separated)</label>
                        <input type="text" wire:model="parsedSkills" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-emerald-400 font-mono text-xs" />
                    </div>

                    @if(!empty($parsedExperiences))
                        <div class="space-y-2 pt-2">
                            <div class="text-xs font-semibold text-slate-300">Extracted Work Experiences ({{ count($parsedExperiences) }})</div>
                            <div class="space-y-2">
                                @foreach($parsedExperiences as $idx => $exp)
                                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs flex justify-between items-center font-mono">
                                        <div>
                                            <span class="text-white font-bold">{{ $exp['role'] ?? 'Role' }}</span> at <span class="text-emerald-400">{{ $exp['company'] ?? 'Company' }}</span>
                                        </div>
                                        <span class="text-slate-500 text-[10px]">{{ $exp['start_date'] ?? '' }} &mdash; {{ $exp['end_date'] ?? 'Present' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-white/5">
                    <button wire:click="$set('step', 1)" class="text-xs text-slate-400 hover:text-white cursor-pointer" data-tooltip="Discard preview and upload another file">
                        &larr; Back to Upload
                    </button>
                    <button wire:click="importToPortfolio" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md cursor-pointer" data-tooltip="Commit extracted data into your portfolio">
                        Import to Portfolio &check;
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

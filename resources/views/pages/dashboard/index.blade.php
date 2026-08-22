<?php

use function Livewire\Volt\{state, layout, title};
use App\Models\Project;
use App\Models\Skill;

layout('layouts.dashboard');
title('Dashboard');

state([
    'projectCount' => fn () => Project::count(),
    'skillCount' => fn () => Skill::count(),
]);

?>

<div>
    <!-- Welcome Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
            Welcome back, Developer!
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Here's what's happening with your portfolio today.
        </p>
    </div>

    <!-- Stats Grid -->
    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        
        <!-- Stat Card 1 -->
        <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6 border border-gray-100 dark:border-gray-800">
            <dt>
                <div class="absolute rounded-lg bg-amber-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total Projects</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $projectCount }}</p>
            </dd>
        </div>

        <!-- Stat Card 2 -->
        <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6 border border-gray-100 dark:border-gray-800">
            <dt>
                <div class="absolute rounded-lg bg-amber-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500 dark:text-gray-400">Skills Added</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $skillCount }}</p>
            </dd>
        </div>

        <!-- Stat Card 3 -->
        <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6 border border-gray-100 dark:border-gray-800">
            <dt>
                <div class="absolute rounded-lg bg-amber-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                    </svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500 dark:text-gray-400">Profile Views</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">1,024</p>
                <p class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                    <svg class="h-5 w-5 flex-shrink-0 self-center text-green-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
                    </svg>
                    <span class="sr-only"> Increased by </span>
                    12%
                </p>
            </dd>
        </div>
    </dl>

    <!-- Recent Activity / Getting Started -->
    <div class="mt-8 overflow-hidden rounded-xl bg-white dark:bg-gray-900 shadow border border-gray-100 dark:border-gray-800">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Getting Started</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                <p>You are now running a clean, custom dashboard powered by Laravel Volt and Tailwind CSS. You have absolute freedom to design this page however you like without fighting Filament's internal CSS classes.</p>
            </div>
            <div class="mt-5">
                <a href="/admin/projects" class="inline-flex items-center rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">
                    Manage Projects
                </a>
            </div>
        </div>
    </div>
</div>

<?php

use function Livewire\Volt\{state, layout, title};

layout('layouts.dashboard');
title('User Dashboard');

state([
    'stats' => [
        ['label' => 'Total Projects', 'value' => '5'],
        ['label' => 'Profile Views', 'value' => '124'],
        ['label' => 'Messages', 'value' => '2'],
    ]
]);

?>

<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}!</h2>
        <p class="text-gray-600 mt-1">Here is a quick overview of your portfolio.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($stats as $stat)
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                <p class="text-3xl font-bold text-green-700 mt-1">{{ $stat['value'] }}</p>
            </div>
            <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Main Content Area -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
        <p class="text-gray-500">Your recent activity will appear here.</p>
    </div>
</div>

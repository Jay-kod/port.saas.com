<?php

use function Livewire\Volt\{state, layout, title};

layout('layouts.super-admin');
title('Super Admin Dashboard');

state([
    'platformStats' => [
        ['label' => 'Total Users', 'value' => '1,024'],
        ['label' => 'Active Subscriptions', 'value' => '856'],
        ['label' => 'MRR', 'value' => '$12,450'],
    ]
]);

?>

<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-100">Welcome, Commander.</h2>
        <p class="text-red-400 mt-1">Platform overview and system health.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($platformStats as $stat)
        <div class="bg-gray-900 rounded-lg p-6 shadow-sm border border-red-900 flex items-center justify-between hover:border-red-600 transition-colors">
            <div>
                <p class="text-sm font-medium text-red-500 uppercase tracking-wider">{{ $stat['label'] }}</p>
                <p class="text-3xl font-bold text-gray-100 mt-1">{{ $stat['value'] }}</p>
            </div>
            <div class="p-3 bg-red-950 text-red-500 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Main Content Area -->
    <div class="bg-gray-900 rounded-lg shadow-sm border border-red-900 p-6">
        <h3 class="text-lg font-semibold text-gray-100 mb-4 border-b border-red-900 pb-2">System Alerts</h3>
        <ul class="space-y-3">
            <li class="flex items-start">
                <span class="flex-shrink-0 h-5 w-5 text-red-500 mt-0.5">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                </span>
                <span class="ml-2 text-gray-300">High CPU usage detected on Worker Node 3.</span>
            </li>
            <li class="flex items-start">
                <span class="flex-shrink-0 h-5 w-5 text-yellow-500 mt-0.5">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                </span>
                <span class="ml-2 text-gray-300">Database backup scheduled in 3 hours.</span>
            </li>
        </ul>
    </div>
</div>

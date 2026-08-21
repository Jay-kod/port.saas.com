@php
    $livewire ??= null;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Custom styling overrides for split auth page */
        .fi-simple-main, .fi-simple-main-ctn, .fi-simple-layout {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
        }
        /* Custom input styling for auth form */
        .filament-custom-auth-form input[type="text"],
        .filament-custom-auth-form input[type="email"],
        .filament-custom-auth-form input[type="password"] {
            background-color: #111827 !important;
            border-color: #1f2937 !important;
            color: #f3f4f6 !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem 1rem !important;
        }
        .filament-custom-auth-form input:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
        }
        .filament-custom-auth-form button[type="submit"] {
            background-color: #ffffff !important;
            color: #030712 !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem 1.5rem !important;
            transition: all 0.2s ease-in-out !important;
        }
        .filament-custom-auth-form button[type="submit"]:hover {
            background-color: #f3f4f6 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.15) !important;
        }
    </style>

    <div class="h-[98vh] max-h-[98vh] w-full flex items-center justify-center p-2 sm:p-3 bg-[#070b0e] text-gray-100 overflow-hidden">
        <main id="fi-main-content" tabindex="-1" class="w-full max-w-5xl h-full max-h-full flex items-center justify-center">
            {{ $slot }}
        </main>
    </div>
</x-filament-panels::layout.base>

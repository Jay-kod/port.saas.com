@props([
    'actions' => [],
    'actionsAlignment' => null,
    'breadcrumbs' => [],
    'heading' => null,
    'subheading' => null,
])

<header
    {{
        $attributes->class([
            'fi-header',
            'fi-header-has-breadcrumbs' => $breadcrumbs,
        ])
    }}
>
    <div>
        @if ($breadcrumbs)
            <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE, scopes: $this->getRenderHookScopes()) }}

        @if (filled($heading))
            <template x-teleport="#custom-topbar-title">
                <h1 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-2xl truncate">
                    {{ $heading }}
                </h1>
            </template>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_HEADING_AFTER, scopes: $this->getRenderHookScopes()) }}


        @if (filled($subheading))
            <p class="fi-header-subheading">
                {{ $subheading }}
            </p>
        @endif
    </div>

    @php
        $beforeActions = \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE, scopes: $this->getRenderHookScopes());
        $afterActions = \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER, scopes: $this->getRenderHookScopes());
    @endphp

    @if (filled($beforeActions) || $actions || filled($afterActions))
        <div class="fi-header-actions-ctn">
            {{ $beforeActions }}

            @if ($actions)
                <x-filament::actions
                    :actions="$actions"
                    :alignment="$actionsAlignment"
                />
            @endif

            {{ $afterActions }}
        </div>
    @endif
</header>

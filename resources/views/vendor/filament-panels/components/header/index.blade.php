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
            'fi-header sticky top-0 z-20 bg-white/75 dark:bg-gray-900/75 backdrop-blur-md py-4 -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8',
            'fi-header-has-breadcrumbs' => $breadcrumbs,
        ])
    }}
>
    <div>
        @if ($breadcrumbs)
            <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
        @endif

        <div class="flex items-center gap-x-4">
            <x-filament::icon-button
                color="gray"
                icon="heroicon-o-bars-3"
                icon-size="lg"
                x-cloak
                x-data="{}"
                x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
            />

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE, scopes: $this->getRenderHookScopes()) }}

            @if (filled($heading))
                <h1 class="fi-header-heading">
                    {{ $heading }}
                </h1>
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_HEADING_AFTER, scopes: $this->getRenderHookScopes()) }}
        </div>

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

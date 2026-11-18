@props(['heading' => '', 'subheading' => ''])

@php
    $links = [
        'profile.edit' => __('Profile'),
        'security.edit' => __('Security'),
        'appearance.edit' => __('Appearance'),
    ];
@endphp

<section class="w-full">
    <div class="mb-6 w-full border-b border-zinc-200 pb-6 dark:border-zinc-700">
        <x-ui.heading size="xl" :level="1">{{ __('Settings') }}</x-ui.heading>
        <x-ui.subheading class="mt-1">{{ __('Manage your profile and account settings') }}</x-ui.subheading>
    </div>

    <div class="flex items-start gap-10 max-md:flex-col max-md:gap-6">
        <nav class="grid w-full gap-1 md:w-[220px]" aria-label="{{ __('Settings') }}">
            @foreach ($links as $route => $label)
                <a
                    href="{{ route($route) }}"
                    @class([
                        'rounded-lg px-3 py-2 text-sm font-medium',
                        'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-white' => request()->routeIs($route),
                        'text-zinc-600 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-700/50' => ! request()->routeIs($route),
                    ])
                    @if (request()->routeIs($route)) aria-current="page" @endif
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="w-full flex-1">
            <x-ui.heading>{{ $heading }}</x-ui.heading>
            <x-ui.subheading>{{ $subheading }}</x-ui.subheading>

            <div class="mt-5 w-full max-w-lg space-y-6">
                <x-flash />

                {{ $slot }}
            </div>
        </div>
    </div>
</section>

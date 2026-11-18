<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-zinc-800 dark:text-white">
        <aside class="fixed inset-y-0 start-0 hidden w-64 flex-col border-e border-zinc-200 bg-zinc-50 p-4 lg:flex dark:border-zinc-700 dark:bg-zinc-900">
            <x-app-logo :href="route('dashboard')" class="mb-6 px-1" />
            @include('partials.navigation')
        </aside>

        <details class="group border-b border-zinc-200 bg-zinc-50 lg:hidden dark:border-zinc-700 dark:bg-zinc-900">
            <summary class="flex h-14 cursor-pointer list-none items-center justify-between px-4 [&::-webkit-details-marker]:hidden">
                <span class="text-sm font-semibold">{{ config('app.name', 'Laravel') }}</span>
                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-300">
                    <span class="group-open:hidden">{{ __('Menu') }}</span>
                    <span class="hidden group-open:inline">{{ __('Fermer') }}</span>
                </span>
            </summary>
            <div class="flex flex-col px-4 pb-4">
                @include('partials.navigation')
            </div>
        </details>

        <main class="p-6 lg:ms-64 lg:p-8">
            {{ $slot }}
        </main>
    </body>
</html>

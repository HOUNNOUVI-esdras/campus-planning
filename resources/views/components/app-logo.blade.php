<a {{ $attributes->class('flex items-center gap-2 font-semibold text-zinc-900 dark:text-white') }}>
    <span class="flex size-8 items-center justify-center rounded-md bg-zinc-800 dark:bg-white">
        <x-app-logo-icon class="size-5 fill-current text-white dark:text-zinc-900" />
    </span>
    <span class="text-sm">{{ config('app.name', 'Laravel') }}</span>
</a>

@props([
    'variant' => 'outline',
    'size' => 'base',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-lg font-medium whitespace-nowrap transition cursor-pointer disabled:opacity-50',
        $size === 'sm' ? 'h-8 px-3 text-sm' : 'h-10 px-4 text-sm',
        match ($variant) {
            'primary' => 'bg-zinc-800 text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200',
            'danger' => 'bg-red-600 text-white hover:bg-red-500',
            'ghost' => 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-700',
            'filled' => 'bg-zinc-100 text-zinc-800 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-white dark:hover:bg-zinc-600',
            default => 'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700',
        },
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif

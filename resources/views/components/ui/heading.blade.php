@props(['size' => 'base', 'level' => 2])

@php
    $classes = match ($size) {
        'xl' => 'text-2xl font-semibold',
        'lg' => 'text-lg font-semibold',
        default => 'text-base font-medium',
    };
@endphp

<h{{ $level }} {{ $attributes->class([$classes, 'text-zinc-900 dark:text-white']) }}>{{ $slot }}</h{{ $level }}>

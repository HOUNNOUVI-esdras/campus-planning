@props([
    'variant' => 'success',
    'heading' => null,
    'icon' => null,
])

<div role="status" {{ $attributes->class([
    'rounded-lg border px-4 py-3 text-sm font-medium',
    match ($variant) {
        'warning' => 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-700 dark:bg-amber-950/50 dark:text-amber-200',
        'danger' => 'border-red-300 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/50 dark:text-red-200',
        default => 'border-green-300 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950/50 dark:text-green-200',
    },
]) }}>
    {{ $heading ?? $slot }}
</div>

@props(['size' => 'base'])

<p {{ $attributes->class([$size === 'sm' ? 'text-xs' : 'text-sm', 'text-zinc-500 dark:text-zinc-400']) }}>{{ $slot }}</p>

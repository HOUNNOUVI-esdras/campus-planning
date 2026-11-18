@props(['href'])

<a href="{{ $href }}" {{ $attributes->class('font-medium text-zinc-900 underline underline-offset-4 hover:text-zinc-600 dark:text-white dark:hover:text-zinc-300') }}>{{ $slot }}</a>

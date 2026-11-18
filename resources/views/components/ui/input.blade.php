@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
])

<div class="grid gap-2">
    @if ($label)
        <x-ui.label :for="$name">{{ $label }}</x-ui.label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ $value }}" @endif
        @error($name) aria-invalid="true" @enderror
        {{ $attributes->class([
            'block h-10 w-full rounded-lg border bg-white px-3 text-sm text-zinc-900 shadow-xs placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-800/20 dark:bg-zinc-900 dark:text-white dark:focus:ring-white/30',
            'border-red-500' => $errors->has($name),
            'border-zinc-200 dark:border-zinc-600' => ! $errors->has($name),
        ]) }}
    />

    <x-ui.error :name="$name" />
</div>

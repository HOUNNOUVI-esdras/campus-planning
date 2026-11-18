@props([
    'name',
    'label',
    'checked' => false,
    'value' => '1',
])

<label class="flex items-center gap-2 text-sm text-zinc-800 dark:text-white">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked($checked) {{ $attributes->class('size-4 rounded border-zinc-300 accent-zinc-800 dark:accent-white') }} />
    {{ $label }}
</label>

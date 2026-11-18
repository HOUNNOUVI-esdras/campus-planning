@props([
    'value' => '',
    'selected' => false,
    'disabled' => false,
])

<option value="{{ $value }}" @selected($selected) @disabled($disabled) {{ $attributes }}>{{ $slot }}</option>

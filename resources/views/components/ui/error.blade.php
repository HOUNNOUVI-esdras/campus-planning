@props(['name'])

@error($name)
    <p role="alert" {{ $attributes->class('text-sm font-medium text-red-600 dark:text-red-400') }}>{{ $message }}</p>
@enderror

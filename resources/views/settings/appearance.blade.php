<x-layouts::app :title="__('Appearance settings')">
    <x-settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div class="inline-flex rounded-lg bg-zinc-100 p-1 dark:bg-zinc-900" role="group" aria-label="{{ __('Appearance') }}">
            @foreach (['light' => __('Light'), 'dark' => __('Dark'), 'system' => __('System')] as $appearance => $label)
                <button
                    type="button"
                    data-appearance="{{ $appearance }}"
                    onclick="setAppearance('{{ $appearance }}')"
                    class="rounded-md px-4 py-1.5 text-sm font-medium text-zinc-600 aria-pressed:bg-white aria-pressed:text-zinc-900 aria-pressed:shadow-sm dark:text-zinc-300 dark:aria-pressed:bg-zinc-700 dark:aria-pressed:text-white"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <script>
            document.querySelectorAll('[data-appearance]').forEach((button) => {
                button.setAttribute('aria-pressed', button.dataset.appearance === (localStorage.getItem('appearance') || 'system'));
            });
        </script>
    </x-settings.layout>
</x-layouts::app>

<x-layouts::app :title="__('Security settings')">
    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form method="POST" action="{{ route('security.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-ui.input name="current_password" :label="__('Current password')" type="password" required autocomplete="current-password" />
            <x-ui.input name="password" :label="__('New password')" type="password" required autocomplete="new-password" />
            <x-ui.input name="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" />

            <x-ui.button variant="primary" type="submit" data-test="update-password-button">
                {{ __('Save') }}
            </x-ui.button>
        </form>
    </x-settings.layout>
</x-layouts::app>

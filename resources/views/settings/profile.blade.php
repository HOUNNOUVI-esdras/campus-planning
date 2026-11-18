<x-layouts::app :title="__('Profile settings')">
    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form method="POST" action="{{ route('profile.update') }}" class="w-full space-y-6">
            @csrf
            @method('PATCH')

            <x-ui.input name="name" :value="old('name', $user->name)" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <x-ui.input name="email" :value="old('email', $user->email)" :label="__('Email')" type="email" required autocomplete="email" />

            <x-ui.button variant="primary" type="submit" data-test="update-profile-button">
                {{ __('Save') }}
            </x-ui.button>
        </form>

        @unless ($user->hasVerifiedEmail())
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-ui.text>
                    {{ __('Your email address is unverified.') }}
                    <button type="submit" class="cursor-pointer text-sm underline">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </x-ui.text>

                @if (session('status') === 'verification-link-sent')
                    <x-ui.text class="mt-2 font-medium !text-green-600 dark:!text-green-400">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </x-ui.text>
                @endif
            </form>
        @else
            <div class="mt-10 space-y-6">
                <div>
                    <x-ui.heading>{{ __('Delete account') }}</x-ui.heading>
                    <x-ui.subheading>{{ __('Delete your account and all of its resources') }}</x-ui.subheading>
                </div>

                <x-ui.button variant="danger" data-test="delete-user-button" onclick="document.getElementById('confirm-user-deletion').showModal()">
                    {{ __('Delete account') }}
                </x-ui.button>

                <dialog id="confirm-user-deletion" class="m-auto w-full max-w-lg rounded-xl bg-white p-6 shadow-lg backdrop:bg-zinc-900/50 dark:bg-zinc-800">
                    <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-6">
                        @csrf
                        @method('DELETE')

                        <div>
                            <x-ui.heading size="lg">{{ __('Are you sure you want to delete your account?') }}</x-ui.heading>
                            <x-ui.subheading>
                                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                            </x-ui.subheading>
                        </div>

                        <x-ui.input name="password" :label="__('Password')" type="password" />

                        <div class="flex justify-end gap-2">
                            <x-ui.button variant="filled" onclick="this.closest('dialog').close()">{{ __('Cancel') }}</x-ui.button>

                            <x-ui.button variant="danger" type="submit" data-test="confirm-delete-user-button">
                                {{ __('Delete account') }}
                            </x-ui.button>
                        </div>
                    </form>
                </dialog>

                @error('password')
                    <script>document.getElementById('confirm-user-deletion').showModal();</script>
                @enderror
            </div>
        @endunless
    </x-settings.layout>
</x-layouts::app>

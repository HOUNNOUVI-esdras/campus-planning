<x-layouts::app :title="__('Groupes')">
    <section class="w-full space-y-6">
        <x-flash />

        <div>
            <x-ui.heading size="xl">{{ __('Groupes') }}</x-ui.heading>
            <x-ui.text class="mt-1">
                {{ __('Les professeurs programment leurs cours pour un groupe. Les étudiants du groupe voient ces cours et reçoivent les notifications.') }}
            </x-ui.text>
        </div>

        @error('group')
            <x-ui.callout variant="danger" :heading="$message" />
        @enderror

        <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
            <div class="order-2 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm lg:order-1 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                    <x-ui.heading size="lg">{{ __('Groupes existants') }}</x-ui.heading>
                    <x-ui.badge>{{ $groups->count() }}</x-ui.badge>
                </div>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($groups as $group)
                        <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <x-ui.text class="font-medium break-words">{{ $group->name }}</x-ui.text>
                                <x-ui.text size="sm" class="mt-1">
                                    {{ trans_choice('{0} Aucun étudiant|{1} :count étudiant|[2,*] :count étudiants', $group->students_count) }}
                                    · {{ trans_choice('{0} Aucun cours|[1,*] :count cours', $group->courses_count) }}
                                </x-ui.text>
                            </div>

                            <div class="flex shrink-0 gap-2">
                                <x-ui.button size="sm" :href="route('admin.groups.edit', $group)">
                                    {{ __('Membres') }}
                                </x-ui.button>

                                @if ($group->courses_count === 0)
                                    <form method="POST" action="{{ route('admin.groups.destroy', $group) }}" onsubmit="return confirm('{{ __('Supprimer ce groupe ?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button size="sm" variant="danger" type="submit">{{ __('Supprimer') }}</x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center">
                            <x-ui.text>{{ __('Aucun groupe pour le moment.') }}</x-ui.text>
                        </div>
                    @endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('admin.groups.store') }}" class="order-1 h-fit space-y-4 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm lg:order-2 dark:border-zinc-700 dark:bg-zinc-900">
                @csrf
                <x-ui.heading size="lg">{{ __('Créer un groupe') }}</x-ui.heading>
                <x-ui.text size="sm">
                    {{ __('Une promotion ou un groupe de TD. Un étudiant peut appartenir à plusieurs groupes.') }}
                </x-ui.text>

                <x-ui.field>
                    <x-ui.label for="name">{{ __('Nom') }}</x-ui.label>
                    <x-ui.input name="name" :value="old('name')" maxlength="100" placeholder="{{ __('Ex. L3 Informatique · TD 1') }}" />
                </x-ui.field>

                <x-ui.button type="submit" variant="primary" class="w-full">
                    {{ __('Créer le groupe') }}
                </x-ui.button>
            </form>
        </div>
    </section>
</x-layouts::app>

@php
    $checkedIds = array_map('intval', old('student_ids', $memberIds));
@endphp

<x-layouts::app :title="$group->name">
    <div class="mx-auto w-full max-w-2xl space-y-6">
        <div>
            <x-ui.link :href="route('admin.groups')" class="text-sm">← {{ __('Retour aux groupes') }}</x-ui.link>
            <x-ui.heading size="xl" class="mt-2 break-words">{{ $group->name }}</x-ui.heading>
            <x-ui.text class="mt-1">{{ __('Cochez les étudiants qui appartiennent à ce groupe.') }}</x-ui.text>
        </div>

        <form method="POST" action="{{ route('admin.groups.update', $group) }}" class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            @csrf
            @method('PUT')

            @if ($students->isEmpty())
                <div class="rounded-lg border border-dashed border-zinc-300 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                    {{ __('Aucun étudiant inscrit sur la plateforme pour le moment.') }}
                </div>
            @else
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($students as $student)
                        <label class="flex items-center gap-3 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                            <input
                                type="checkbox"
                                name="student_ids[]"
                                value="{{ $student->id }}"
                                @checked(in_array($student->id, $checkedIds, true))
                                class="size-4 rounded border-zinc-300 accent-zinc-800 dark:accent-white"
                            />
                            <span class="text-zinc-800 dark:text-zinc-100">{{ $student->name }}</span>
                        </label>
                    @endforeach
                </div>
            @endif

            <x-ui.error name="student_ids" />
            <x-ui.error name="student_ids.*" />

            <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <x-ui.button variant="ghost" :href="route('admin.groups')">{{ __('Annuler') }}</x-ui.button>
                <x-ui.button variant="primary" type="submit">{{ __('Enregistrer') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts::app>

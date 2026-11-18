<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupController extends Controller
{
    /**
     * List the groups with their number of students and courses.
     */
    public function index(): View
    {
        return view('admin.groups.index', [
            'groups' => Group::query()->withCount(['students', 'courses'])->orderBy('name')->get(),
        ]);
    }

    /**
     * Create a group.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:groups,name'],
        ], [
            'name.required' => 'Donnez un nom au groupe.',
            'name.max' => 'Le nom ne peut pas dépasser 100 caractères.',
            'name.unique' => 'Ce groupe existe déjà.',
        ]);

        Group::query()->create($validated);

        return to_route('admin.groups')->with('status', 'Groupe créé.');
    }

    /**
     * Show the students of a group, with a box to tick for each student.
     */
    public function edit(Group $group): View
    {
        return view('admin.groups.edit', [
            'group' => $group,
            'students' => User::query()->where('role', Role::Student->value)->orderBy('name')->get(),
            'memberIds' => $group->students()->pluck('users.id')->all(),
        ]);
    }

    /**
     * Replace the students of a group.
     */
    public function update(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => ['array'],
            'student_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', Role::Student->value)],
        ], [
            'student_ids.array' => 'La liste des membres est invalide.',
            'student_ids.*.integer' => 'Un membre sélectionné est invalide.',
            'student_ids.*.exists' => 'Seuls les étudiants peuvent être membres d’un groupe.',
        ]);

        $group->students()->sync($validated['student_ids'] ?? []);

        return to_route('admin.groups')->with('status', 'Membres du groupe enregistrés.');
    }

    /**
     * Delete a group that no course uses.
     */
    public function destroy(Group $group): RedirectResponse
    {
        if ($group->courses()->exists()) {
            return back()->withErrors([
                'group' => 'Ce groupe suit au moins un cours, il ne peut donc pas être supprimé.',
            ]);
        }

        $group->delete();

        return to_route('admin.groups')->with('status', 'Groupe supprimé.');
    }
}

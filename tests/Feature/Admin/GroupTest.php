<?php

use App\Models\Course;
use App\Models\Group;
use App\Models\User;

test('an admin can visit the groups page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.groups'))
        ->assertOk()
        ->assertSee('Groupes');
});

test('a professor cannot visit the groups page', function () {
    $professor = User::factory()->professor()->create();

    $this->actingAs($professor)
        ->get(route('admin.groups'))
        ->assertForbidden();
});

test('a student cannot visit the groups page', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('admin.groups'))
        ->assertForbidden();
});

test('a guest is redirected from the groups page', function () {
    $this->get(route('admin.groups'))
        ->assertRedirect(route('login'));
});

test('an admin can create a group', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.groups.store'), ['name' => 'L3 Informatique'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.groups'));

    expect(Group::query()->sole()->name)->toBe('L3 Informatique');
});

test('a group requires a name', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.groups.store'), ['name' => ''])
        ->assertSessionHasErrors(['name' => 'Donnez un nom au groupe.']);

    expect(Group::query()->count())->toBe(0);
});

test('a group name must be unique', function () {
    $admin = User::factory()->admin()->create();
    Group::factory()->create(['name' => 'L3 Informatique']);

    $this->actingAs($admin)
        ->post(route('admin.groups.store'), ['name' => 'L3 Informatique'])
        ->assertSessionHasErrors(['name' => 'Ce groupe existe déjà.']);

    expect(Group::query()->count())->toBe(1);
});

test('an admin sees the current students of a group', function () {
    $admin = User::factory()->admin()->create();
    $chloe = User::factory()->student()->create(['name' => 'Chloé Kpadonou']);
    $group = Group::factory()->create();
    $group->students()->attach($chloe);

    $this->actingAs($admin)
        ->get(route('admin.groups.edit', $group))
        ->assertOk()
        ->assertSee('Chloé Kpadonou');
});

test('an admin can choose the students of a group', function () {
    $admin = User::factory()->admin()->create();
    $alice = User::factory()->student()->create();
    $bob = User::factory()->student()->create();
    $chloe = User::factory()->student()->create();
    $group = Group::factory()->create();
    $group->students()->attach($chloe);

    $this->actingAs($admin)
        ->put(route('admin.groups.update', $group), ['student_ids' => [$alice->id, $bob->id]])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.groups'));

    expect($group->students()->pluck('users.id')->all())->toEqualCanonicalizing([$alice->id, $bob->id]);
});

test('an admin can empty a group', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();
    $group->students()->attach(User::factory()->student()->create());

    $this->actingAs($admin)
        ->put(route('admin.groups.update', $group), [])
        ->assertSessionHasNoErrors();

    expect($group->students()->count())->toBe(0);
});

test('only students can be members of a group', function () {
    $admin = User::factory()->admin()->create();
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $this->actingAs($admin)
        ->put(route('admin.groups.update', $group), ['student_ids' => [$professor->id]])
        ->assertSessionHasErrors(['student_ids.0' => 'Seuls les étudiants peuvent être membres d’un groupe.']);

    expect($group->students()->count())->toBe(0);
});

test('a professor cannot change the members of a group', function () {
    $professor = User::factory()->professor()->create();
    $student = User::factory()->student()->create();
    $group = Group::factory()->create();

    $this->actingAs($professor)
        ->put(route('admin.groups.update', $group), ['student_ids' => [$student->id]])
        ->assertForbidden();

    expect($group->students()->count())->toBe(0);
});

test('an admin can delete a group that no course uses', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.groups.destroy', $group))
        ->assertRedirect(route('admin.groups'));

    expect($group->fresh())->toBeNull();
});

test('a group used by a course cannot be deleted', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();
    Course::factory()->for($group)->create();

    $this->actingAs($admin)
        ->delete(route('admin.groups.destroy', $group))
        ->assertSessionHasErrors(['group' => 'Ce groupe suit au moins un cours, il ne peut donc pas être supprimé.']);

    expect($group->fresh())->not->toBeNull();
});

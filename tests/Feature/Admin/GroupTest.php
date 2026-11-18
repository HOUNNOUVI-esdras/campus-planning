<?php

use App\Models\Course;
use App\Models\Group;
use App\Models\User;

test('an admin can see the groups page', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.groups'));

    $response->assertOk();
});

test('a professor cannot see the groups page', function () {
    $professor = User::factory()->professor()->create();

    $response = $this->actingAs($professor)->get(route('admin.groups'));

    $response->assertForbidden();
});

test('an admin can create a group', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.groups.store'), ['name' => 'L3 Informatique']);

    $this->assertDatabaseHas('groups', ['name' => 'L3 Informatique']);
});

test('a group needs a name', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.groups.store'), ['name' => '']);

    $response->assertSessionHasErrors('name');
    $this->assertDatabaseCount('groups', 0);
});

test('two groups cannot have the same name', function () {
    $admin = User::factory()->admin()->create();
    Group::factory()->create(['name' => 'L3 Informatique']);

    $response = $this->actingAs($admin)->post(route('admin.groups.store'), ['name' => 'L3 Informatique']);

    $response->assertSessionHasErrors('name');
    $this->assertDatabaseCount('groups', 1);
});

test('an admin can choose the students of a group', function () {
    $admin = User::factory()->admin()->create();
    $alice = User::factory()->student()->create();
    $group = Group::factory()->create();

    $this->actingAs($admin)->put(route('admin.groups.update', $group), ['student_ids' => [$alice->id]]);

    $this->assertDatabaseHas('group_student', ['group_id' => $group->id, 'user_id' => $alice->id]);
});

test('only students can be members of a group', function () {
    $admin = User::factory()->admin()->create();
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($admin)->put(route('admin.groups.update', $group), ['student_ids' => [$professor->id]]);

    $response->assertSessionHasErrors('student_ids.0');
    $this->assertDatabaseCount('group_student', 0);
});

test('an admin can delete a group that no course uses', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();

    $this->actingAs($admin)->delete(route('admin.groups.destroy', $group));

    $this->assertDatabaseMissing('groups', ['id' => $group->id]);
});

test('a group used by a course cannot be deleted', function () {
    $admin = User::factory()->admin()->create();
    $group = Group::factory()->create();
    Course::factory()->for($group)->create();

    $this->actingAs($admin)->delete(route('admin.groups.destroy', $group));

    $this->assertDatabaseHas('groups', ['id' => $group->id]);
});

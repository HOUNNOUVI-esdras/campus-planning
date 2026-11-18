<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('security settings page can be rendered', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('security.edit'))
        ->assertOk()
        ->assertSee('Update password');
});

test('password can be updated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->put(route('security.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('security.edit'));

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->put(route('security.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors(['current_password']);

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

test('the new password must be confirmed', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->put(route('security.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'another-password',
        ])
        ->assertSessionHasErrors(['password']);

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

<?php

namespace App\Http\Controllers\Settings;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Show the profile form.
     */
    public function edit(Request $request): View
    {
        return view('settings.profile', ['user' => $request->user()]);
    }

    /**
     * Update the name and email of the signed-in user.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->fill($request->validate($this->profileRules($user->id)));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile.edit')->with('status', __('Profile updated.'));
    }

    /**
     * Delete the signed-in user after checking their password.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        /** @var User $user */
        $user = $request->user();

        Auth::guard('web')->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

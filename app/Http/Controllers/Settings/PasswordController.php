<?php

namespace App\Http\Controllers\Settings;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordController extends Controller
{
    use PasswordValidationRules;

    /**
     * Show the password form.
     */
    public function edit(): View
    {
        return view('settings.security');
    }

    /**
     * Change the password of the signed-in user.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => $this->currentPasswordRules(),
            'password' => $this->passwordRules(),
        ]);

        /** @var User $user */
        $user = $request->user();

        $user->update(['password' => $validated['password']]);

        return to_route('security.edit')->with('status', __('Password updated.'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SpecialDay;
use Illuminate\Http\Request;

class CancelSpecialDayController extends Controller
{
    public function __invoke(Request $request, SpecialDay $specialDay)
    {
abort_unless($request->user()->isAdmin(), 403);
        $specialDay->update(['status' => 'annule']);

        return back()->with('success', 'Jour spécial annulé.');
    }
}
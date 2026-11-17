<?php

namespace App\Http\Controllers;

use App\Models\SpecialDay;

class CancelSpecialDayController extends Controller
{
    public function __invoke(SpecialDay $specialDay)
    {
        $specialDay->update(['status' => 'annule']);

        return back()->with('success', 'Jour spécial annulé.');
    }
}
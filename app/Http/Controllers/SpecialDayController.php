<?php

namespace App\Http\Controllers;

use App\Models\SpecialDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class SpecialDayController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->user()->isProfessor(), 403);

        $data = $request->validate([
            'libelle'    => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin'   => 'nullable|date|after_or_equal:date_debut',
        ]);

        $debut = Carbon::parse($data['date_debut']);
        $fin   = Carbon::parse($data['date_fin'] ?? $data['date_debut']);

        $jours = collect(CarbonPeriod::create($debut, $fin))
            ->map(fn ($jour) => $jour->toDateString());

        if (SpecialDay::whereIn('date', $jours)->exists()) {
            return back()->withErrors(['date_debut' => 'Une de ces dates existe déjà.'])->withInput();
        }

        foreach ($jours as $jour) {
            SpecialDay::create(['date' => $jour, 'libelle' => $data['libelle']]);
        }

        return back()->with('success', 'Jour(s) spécial(aux) créé(s).');
    }

    public function update(Request $request, SpecialDay $specialDay)
    {
        abort_unless($request->user()->isProfessor(), 403);

        $data = $request->validate(['libelle' => 'required|string|max:255']);
        $specialDay->update($data);

        return back()->with('success', 'Libellé modifié.');
    }
}
<?php

use App\Http\Controllers\CancelSpecialDayController;
use App\Http\Controllers\SpecialDayController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::calendar')->name('dashboard');

    Route::post('/special-days', [SpecialDayController::class, 'store'])->name('special-days.store');
    Route::patch('/special-days/{specialDay}', [SpecialDayController::class, 'update'])->name('special-days.update');
    Route::patch('/special-days/{specialDay}/cancel', CancelSpecialDayController::class)->name('special-days.cancel');
});

require __DIR__.'/settings.php';
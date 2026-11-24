<?php

use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CancelSpecialDayController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SpecialDayController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', CalendarController::class)->name('dashboard');

    Route::resource('courses', CourseController::class)->except(['index', 'show']);

    Route::post('special-days', [SpecialDayController::class, 'store'])->name('special-days.store');
    Route::patch('special-days/{specialDay}', [SpecialDayController::class, 'update'])->name('special-days.update');
    Route::patch('special-days/{specialDay}/cancel', CancelSpecialDayController::class)->name('special-days.cancel');

    Route::middleware('can:manage-groups')->prefix('admin/groups')->name('admin.groups')->group(function () {
        Route::get('/', [GroupController::class, 'index']);
        Route::post('/', [GroupController::class, 'store'])->name('.store');
        Route::get('{group}/edit', [GroupController::class, 'edit'])->name('.edit');
        Route::put('{group}', [GroupController::class, 'update'])->name('.update');
        Route::delete('{group}', [GroupController::class, 'destroy'])->name('.destroy');
    });
});

require __DIR__.'/settings.php';
<?php

use App\Http\Controllers\BoxController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ReservationController;
use App\Models\Eventos;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),      
    ]);
})->name('home');


Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('eventos', EventosController::class);
    Route::resource('products', ProductsController::class);
    Route::resource('box', BoxController::class);
});

require __DIR__ . '/settings.php';

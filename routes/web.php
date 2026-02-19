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

//RUTAS PUBLICAS QUE NO REQUIEREN AUTENTICACIÓN
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),      
    ]);
})->name('home');

//RUTAS DE CALENDARIO PUBLICO   
Route::get('/reservations/busy', [ReservationController::class, 'getReservasOcupadas'])->name('reservations.busy');
Route::get('/events/type_events', [ReservationController::class, 'getTypeEvents'])->name('events.type_events');

//ENVIAR DATOS DEL FORMULARIO DE RESERVA DESDE EL INICIO
Route::post('/reservations/customers', [ReservationController::class, 'store'])->name('reservations.customers');

//RUTAS QUE REQUIEREN AUTENTICACIÓN
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('eventos', EventosController::class);
    Route::resource('products', ProductsController::class);
    Route::resource('box', BoxController::class);

    
    Route::resource('reservations', ReservationController::class);
    // Route::post('/reservations', [ReservationController::class, 'create'])->name('reservations.create');
});


require __DIR__ . '/settings.php';

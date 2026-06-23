<?php

use App\Http\Controllers\BoxController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ReservationAssistantController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// RUTAS PUBLICAS QUE NO REQUIEREN AUTENTICACIÓN
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// RUTAS DE CALENDARIO PUBLICO
Route::get('/reservations/busy', [ReservationController::class, 'getReservasOcupadas'])->name('reservations.busy');
Route::get('/events/type_events', [ReservationController::class, 'getTypeEvents'])->name('events.type_events');

// ENVIAR DATOS DEL FORMULARIO DE RESERVA DESDE EL INICIO
Route::post('/reservations/customers', [ReservationController::class, 'store'])->name('reservations.customers');
Route::post('/reservation-assistant', ReservationAssistantController::class)
    ->middleware('throttle:20,1')
    ->name('reservation.assistant');

// RUTAS QUE REQUIEREN AUTENTICACIÓN
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('eventos', EventosController::class);
    Route::resource('products', ProductsController::class);
    Route::resource('box', BoxController::class);
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/reservations', [CalendarController::class, 'store'])->name('calendar.reservations.store');
    Route::patch('/calendar/release', [CalendarController::class, 'release'])->name('calendar.release');

    Route::patch('/reservations/{reservation}/approve-payment', [ReservationController::class, 'approvePayment'])
        ->name('reservations.approve_payment');
    Route::patch('/reservations/{reservation}/reject-payment', [ReservationController::class, 'rejectPayment'])
        ->name('reservations.reject_payment');
    Route::resource('reservations', ReservationController::class);
    // Route::post('/reservations', [ReservationController::class, 'create'])->name('reservations.create');
});

require __DIR__.'/settings.php';

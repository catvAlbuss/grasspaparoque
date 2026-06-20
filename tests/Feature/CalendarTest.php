<?php

use App\Models\Customer;
use App\Models\Eventos;
use App\Models\Reservation;
use App\Models\User;

test('calendar requires authentication', function () {
    $this->get(route('calendar.index'))->assertRedirect(route('login'));
});

test('authenticated users can view the calendar', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('calendar.index'))
        ->assertOk();
});

test('an authenticated user can occupy a free calendar space', function () {
    $event = Eventos::create([
        'nombre' => 'Fútbol',
        'tipo' => 'cancha',
        'precio' => 50,
        'descripcion' => 'Cancha principal',
        'estado' => 'free',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('calendar.reservations.store'), [
            'id_evento' => $event->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'name' => 'Cliente',
            'lastname' => 'Prueba',
            'phone' => '999999999',
            'email' => '',
        ])
        ->assertRedirect(route('calendar.index'));

    $this->assertDatabaseHas('reservations', [
        'id_evento' => $event->id,
        'start_time' => '10:00',
        'end_time' => '11:00',
        'reservation_status' => 'busy',
        'payment_status' => 'approved',
    ]);
});

test('an occupied calendar space cannot be overlapped', function () {
    $event = Eventos::create([
        'nombre' => 'Vóley',
        'tipo' => 'cancha',
        'precio' => 50,
        'descripcion' => 'Cancha principal',
        'estado' => 'free',
    ]);
    $customer = Customer::create([
        'name' => 'Primero',
        'lastname' => 'Cliente',
        'phone' => '999999998',
        'email' => 'primero@example.com',
    ]);
    $date = now()->addDay()->format('Y-m-d');
    Reservation::create([
        'id_evento' => $event->id,
        'id_customer' => $customer->id,
        'date' => $date,
        'start_time' => '10:00',
        'end_time' => '12:00',
        'reservation_status' => 'busy',
        'payment_status' => 'approved',
    ]);

    $this->actingAs(User::factory()->create())
        ->from(route('calendar.index'))
        ->post(route('calendar.reservations.store'), [
            'id_evento' => $event->id,
            'date' => $date,
            'start_time' => '11:00',
            'end_time' => '13:00',
            'name' => 'Segundo',
            'lastname' => 'Cliente',
            'phone' => '999999997',
            'email' => '',
        ])
        ->assertRedirect(route('calendar.index'))
        ->assertSessionHasErrors('start_time');

    expect(Reservation::count())->toBe(1);
});

test('a pending calendar space is shown and cannot be occupied administratively', function () {
    $event = Eventos::create([
        'nombre' => 'Evento pendiente',
        'tipo' => 'evento',
        'precio' => 100,
        'descripcion' => 'Evento de prueba',
        'estado' => 'free',
    ]);
    $customer = Customer::create([
        'name' => 'Cliente',
        'lastname' => 'Pendiente',
        'phone' => '999999996',
        'email' => 'pendiente@example.com',
    ]);
    $date = now()->addDays(2)->format('Y-m-d');
    Reservation::create([
        'id_evento' => $event->id,
        'id_customer' => $customer->id,
        'date' => $date,
        'start_time' => '15:00',
        'end_time' => '16:00',
        'reservation_status' => 'free',
        'payment_status' => 'pending',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('calendar.reservations.store'), [
            'id_evento' => $event->id,
            'date' => $date,
            'start_time' => '15:00',
            'end_time' => '16:00',
            'name' => 'Otro',
            'lastname' => 'Cliente',
            'phone' => '999999995',
            'email' => '',
        ])
        ->assertSessionHasErrors('start_time');

    expect(Reservation::count())->toBe(1);
});

test('a pending reservation expires and releases its space after ten minutes', function () {
    $event = Eventos::create([
        'nombre' => 'Reserva temporal',
        'tipo' => 'cancha',
        'precio' => 50,
        'descripcion' => 'Cancha temporal',
        'estado' => 'free',
    ]);
    $customer = Customer::create([
        'name' => 'Cliente',
        'lastname' => 'Temporal',
        'phone' => '999999994',
        'email' => 'temporal@example.com',
    ]);
    $reservation = Reservation::create([
        'id_evento' => $event->id,
        'id_customer' => $customer->id,
        'date' => now()->addDay()->format('Y-m-d'),
        'start_time' => '12:00',
        'end_time' => '13:00',
        'reservation_status' => 'free',
        'payment_status' => 'pending',
    ]);

    $this->travel(11)->minutes();
    $this->actingAs(User::factory()->create())
        ->get(route('calendar.index'))
        ->assertOk();

    expect($reservation->fresh()->payment_status)->toBe('rejected')
        ->and($reservation->fresh()->reservation_status)->toBe('free');
});

test('occupied reservations can be released by hours day and week', function () {
    $event = Eventos::create([
        'nombre' => 'Cancha liberable',
        'tipo' => 'cancha',
        'precio' => 50,
        'descripcion' => 'Cancha de prueba',
        'estado' => 'free',
    ]);
    $customer = Customer::create([
        'name' => 'Cliente',
        'lastname' => 'Cancelación',
        'phone' => '999999993',
        'email' => 'cancelacion@example.com',
    ]);
    $monday = now()->next('Monday')->startOfDay();
    $makeReservation = fn (string $date, string $start, string $end) => Reservation::create([
        'id_evento' => $event->id,
        'id_customer' => $customer->id,
        'date' => $date,
        'start_time' => $start,
        'end_time' => $end,
        'reservation_status' => 'busy',
        'payment_status' => 'approved',
    ]);

    $first = $makeReservation($monday->format('Y-m-d'), '08:00', '09:00');
    $second = $makeReservation($monday->format('Y-m-d'), '10:00', '11:00');
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('calendar.release'), [
        'scope' => 'hours',
        'id_evento' => $event->id,
        'date' => $monday->format('Y-m-d'),
        'reservation_id' => $first->id,
    ])->assertRedirect(route('calendar.index'));

    expect($first->fresh()->reservation_status)->toBe('free')
        ->and($second->fresh()->reservation_status)->toBe('busy');

    $this->actingAs($user)->patch(route('calendar.release'), [
        'scope' => 'day',
        'id_evento' => $event->id,
        'date' => $monday->format('Y-m-d'),
        'reservation_id' => null,
    ])->assertRedirect(route('calendar.index'));

    expect($second->fresh()->reservation_status)->toBe('free');

    $insideWeek = $makeReservation($monday->copy()->addDays(2)->format('Y-m-d'), '14:00', '15:00');
    $nextWeek = $makeReservation($monday->copy()->addWeek()->format('Y-m-d'), '14:00', '15:00');

    $this->actingAs($user)->patch(route('calendar.release'), [
        'scope' => 'week',
        'id_evento' => $event->id,
        'date' => $monday->copy()->addDays(2)->format('Y-m-d'),
        'reservation_id' => null,
    ])->assertRedirect(route('calendar.index'));

    expect($insideWeek->fresh()->reservation_status)->toBe('free')
        ->and($nextWeek->fresh()->reservation_status)->toBe('busy');
});

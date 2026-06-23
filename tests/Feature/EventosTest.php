<?php

use App\Models\Eventos;
use App\Models\User;

test('event status can be loaded and updated with its database values', function () {
    $evento = Eventos::create([
        'nombre' => 'Cancha de prueba',
        'precio' => 50,
        'descripcion' => 'Descripción inicial',
        'estado' => 'free',
        'tipo_ambiente' => 'propio',
        'ambiente_grupo' => null,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('eventos.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('events/index')
            ->where('eventos.0.estado', 'free'));

    $this->actingAs($user)
        ->put(route('eventos.update', $evento), [
            'nombre' => $evento->nombre,
            'precio' => $evento->precio,
            'descripcion' => $evento->descripcion,
            'estado' => 'busy',
            'tipo_ambiente' => 'propio',
            'ambiente_grupo' => null,
        ])
        ->assertRedirect(route('eventos.index'));

    expect($evento->fresh()->estado)->toBe('busy');
});

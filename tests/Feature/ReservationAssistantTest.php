<?php

use Illuminate\Support\Facades\Http;

test('reservation assistant validates its input', function () {
    $this->postJson(route('reservation.assistant'), [])->assertUnprocessable();
});

test('reservation assistant fails safely without an api key', function () {
    config(['services.openai.api_key' => null]);

    $this->postJson(route('reservation.assistant'), [
        'question' => '¿Cómo reservo?',
        'step' => 'date',
    ])->assertServiceUnavailable()->assertJsonStructure(['message']);
});

test('reservation assistant returns the model response', function () {
    config(['services.openai.api_key' => 'test-key']);
    Http::fake([
        'api.openai.com/*' => Http::response([
            'output' => [[
                'content' => [['type' => 'output_text', 'text' => 'Selecciona una fecha en el calendario.']],
            ]],
        ]),
    ]);

    $this->postJson(route('reservation.assistant'), [
        'question' => '¿Cómo elijo una fecha?',
        'step' => 'date',
        'reservation' => ['hours' => 2],
    ])->assertOk()->assertJson(['message' => 'Selecciona una fecha en el calendario.']);

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-key'));
});

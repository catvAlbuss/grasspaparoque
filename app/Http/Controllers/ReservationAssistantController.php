<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReservationAssistantController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'step' => ['required', 'string', 'max:30'],
            'reservation' => ['sometimes', 'array'],
            'reservation.event' => ['nullable', 'string', 'max:100'],
            'reservation.date' => ['nullable', 'date_format:Y-m-d'],
            'reservation.start_time' => ['nullable', 'date_format:H:i'],
            'reservation.hours' => ['nullable', 'integer', 'between:1,8'],
        ]);

        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            return response()->json(['message' => $this->fallbackMessage()], 503);
        }

        $context = json_encode($data['reservation'] ?? [], JSON_UNESCAPED_UNICODE);

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(12)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => config('services.openai.model'),
                    'instructions' => implode(' ', [
                        'Eres el asistente de reservas de Grasspaparoque en Perú.',
                        'Responde en español claro, amable y breve, con máximo 80 palabras.',
                        'Ayuda a entender el proceso, pero nunca inventes disponibilidad, precios, políticas ni confirmes reservas.',
                        'Indica al usuario que use los selectores del formulario para fecha, hora y duración.',
                        'No solicites DNI, teléfono, datos bancarios ni otros datos personales en esta conversación de ayuda.',
                    ]),
                    'input' => "Paso actual: {$data['step']}. Contexto no sensible: {$context}. Pregunta: {$data['question']}",
                    'max_output_tokens' => 180,
                ]);

            if ($response->failed()) {
                Log::warning('OpenAI reservation assistant failed', ['status' => $response->status()]);

                return response()->json(['message' => $this->fallbackMessage()], 503);
            }

            $message = $this->extractText($response->json());

            return response()->json(['message' => $message ?: $this->fallbackMessage()]);
        } catch (ConnectionException $exception) {
            Log::warning('OpenAI reservation assistant unavailable', ['message' => $exception->getMessage()]);

            return response()->json(['message' => $this->fallbackMessage()], 503);
        }
    }

    private function extractText(array $payload): ?string
    {
        if (is_string($payload['output_text'] ?? null)) {
            return trim($payload['output_text']);
        }

        foreach ($payload['output'] ?? [] as $output) {
            foreach ($output['content'] ?? [] as $content) {
                if (($content['type'] ?? null) === 'output_text' && is_string($content['text'] ?? null)) {
                    return trim($content['text']);
                }
            }
        }

        return null;
    }

    private function fallbackMessage(): string
    {
        return 'La ayuda inteligente no está disponible ahora. Continúa con las opciones del formulario o comunícate por WhatsApp si necesitas atención especial.';
    }
}

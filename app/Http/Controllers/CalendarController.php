<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Eventos;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index()
    {
        Reservation::expirePending();

        return Inertia::render('calendar/index', [
            'eventos' => Eventos::query()
                ->select('id', 'nombre', 'precio', 'tipo_ambiente', 'ambiente_grupo')
                ->orderBy('nombre')
                ->get(),
            'reservations' => Reservation::query()
                ->blocking()
                ->whereDate('date', '>=', now()->startOfMonth()->subMonth())
                ->with('customer:id,name,lastname')
                ->select('id', 'id_evento', 'id_customer', 'date', 'start_time', 'end_time', 'reservation_status', 'payment_status', 'created_at')
                ->orderBy('date')
                ->orderBy('start_time')
                ->get()
                ->map(fn (Reservation $reservation) => [
                    'id' => $reservation->id,
                    'id_evento' => $reservation->id_evento,
                    'date' => $reservation->date,
                    'start_time' => $reservation->start_time,
                    'end_time' => $reservation->end_time,
                    'status' => $reservation->reservation_status === 'busy' ? 'occupied' : 'pending',
                    'expires_at' => $reservation->payment_status === 'pending'
                        ? $reservation->created_at->addMinutes(Reservation::PENDING_TIMEOUT_MINUTES)->toIso8601String()
                        : null,
                    'customer_name' => trim(($reservation->customer?->name ?? '') . ' ' . ($reservation->customer?->lastname ?? '')),
                ]),
        ]);
    }

    public function store(Request $request)
    {
        Reservation::expirePending();

        $validated = $request->validate([
            'id_evento' => ['required', 'integer', Rule::exists('eventos', 'id')],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $evento = Eventos::select('id', 'tipo_ambiente', 'ambiente_grupo')->findOrFail($validated['id_evento']);
        $eventoIds = $this->resolveGrupoIds($evento);

        DB::transaction(function () use ($validated, $eventoIds) {
            $hasConflict = Reservation::query()
                ->whereIn('id_evento', $eventoIds)
                ->where('date', $validated['date'])
                ->blocking()
                ->where('start_time', '<', $validated['end_time'])
                ->where('end_time', '>', $validated['start_time'])
                ->lockForUpdate()
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'start_time' => 'Ese horario está ocupado o pendiente de confirmación. Elige otro espacio.',
                ]);
            }

            $safePhone = preg_replace('/\D+/', '', $validated['phone']);
            $customer = Customer::create([
                'name' => trim($validated['name']),
                'lastname' => trim($validated['lastname'] ?? '') ?: '.',
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?: 'reserva_' . ($safePhone ?: uniqid()) . '@local.test',
            ]);

            Reservation::create([
                'id_evento' => $validated['id_evento'],
                'id_customer' => $customer->id,
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'reservation_status' => 'busy',
                'payment_status' => 'approved',
                'payment_reviewed_at' => now(),
            ]);
        });

        return to_route('calendar.index')->with('success', 'Horario ocupado correctamente.');
    }

    public function release(Request $request)
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:hours,day,week'],
            'id_evento' => ['required', 'integer', Rule::exists('eventos', 'id')],
            'date' => ['required', 'date_format:Y-m-d'],
            'reservation_id' => ['nullable', 'integer', Rule::exists('reservations', 'id')],
        ]);

        $query = Reservation::query()
            ->where('id_evento', $validated['id_evento'])
            ->where('reservation_status', 'busy');

        if ($validated['scope'] === 'hours') {
            if (empty($validated['reservation_id'])) {
                throw ValidationException::withMessages([
                    'reservation_id' => 'Selecciona la reserva que deseas liberar.',
                ]);
            }
            $query->whereKey($validated['reservation_id']);
        } elseif ($validated['scope'] === 'day') {
            $query->whereDate('date', $validated['date']);
        } else {
            $date = Carbon::createFromFormat('Y-m-d', $validated['date']);
            $query->whereBetween('date', [
                $date->copy()->startOfWeek()->format('Y-m-d'),
                $date->copy()->endOfWeek()->format('Y-m-d'),
            ]);
        }

        $released = $query->update([
            'reservation_status' => 'free',
            'payment_status' => 'rejected',
            'payment_reviewed_at' => now(),
        ]);

        if ($released === 0) {
            throw ValidationException::withMessages([
                'scope' => 'No se encontraron reservas ocupadas para liberar.',
            ]);
        }

        return to_route('calendar.index')->with('success', "Se liberaron {$released} reserva(s).");
    }

    private function resolveGrupoIds(Eventos $evento): array
    {
        if ($evento->tipo_ambiente === 'compartido' && $evento->ambiente_grupo) {
            return Eventos::where('ambiente_grupo', $evento->ambiente_grupo)
                ->pluck('id')
                ->toArray();
        }

        return [$evento->id];
    }
}

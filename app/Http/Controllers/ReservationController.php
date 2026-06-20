<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Eventos;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservations = Reservation::with(['evento:id,nombre', 'pay:id,amount', 'customer:id,name'])
            ->latest()
            ->get()
            ->map(fn(Reservation $reservation) => [
                'id' => $reservation->id,
                'id_evento' => $reservation->id_evento,
                'id_pay' => $reservation->id_pay,
                'id_customer' => $reservation->id_customer,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'date' => $reservation->date,
                'reservation_status' => $reservation->reservation_status,
                'payment_status' => $reservation->payment_status,
                'payment_reviewed_at' => $reservation->payment_reviewed_at,
                'payment_proof_name' => $reservation->payment_proof_name,
                'payment_proof_number' => $reservation->payment_proof_number,
                'event_name' => $reservation->evento?->nombre,
                'pay_amount' => $reservation->pay?->amount,
                'customer_name' => $reservation->customer?->name,
                'customer_lastname' => $reservation->customer?->lastname,
                'customer_dni' => $reservation->customer?->dni,
                'customer_phone' => $reservation->customer?->phone,
            ]);

        return Inertia::render('reservations/index', [
            'reservations' => $reservations,
            'eventos' => Eventos::select('id', 'nombre', 'precio')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return to_route('reservations.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Reservation::expirePending();

        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'lastname'             => ['nullable', 'string', 'max:255'],
            'email'                => ['nullable', 'email', 'max:255'],
            'phone'                => ['required', 'string', 'max:20'],
            'id_evento'            => ['required', 'integer', Rule::exists('eventos', 'id')],
            'date'                 => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time'           => ['required', 'date_format:H:i'],
            'end_time'             => ['required', 'date_format:H:i', 'after:start_time'],
            'payment_proof_number' => ['nullable', 'string', 'max:100'],
            'payment_proof_file'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        [$customerName, $customerLastname] = $this->normalizeCustomerName(
            $validated['name'],
            $validated['lastname'] ?? null
        );

        $email = $validated['email'] ?? null;
        if ($email === null || $email === '') {
            $safePhone = preg_replace('/\D+/', '', $validated['phone']);
            $email = 'reserva_' . ($safePhone ?: time()) . '@local.test';
        }

        $eventoIds = $this->resolveGrupoIds(Eventos::select('id', 'tipo_ambiente', 'ambiente_grupo')->findOrFail($validated['id_evento']));

        DB::transaction(function () use ($validated, $request, $customerName, $customerLastname, $email, $eventoIds) {
            $hasConflict = Reservation::query()
                ->whereIn('id_evento', $eventoIds)
                ->where('date', $validated['date'])
                ->blocking()
                ->where('start_time', '<', $validated['end_time'])
                ->where('end_time', '>', $validated['start_time'])
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'start_time' => 'Ya existe una reserva ocupada en ese horario.',
                ]);
            }

            $customer = Customer::create([
                'name'     => $customerName,
                'lastname' => $customerLastname,
                'email'    => $email,
                'phone'    => $validated['phone'],
            ]);

            $storedProofName = null;
            if ($request->hasFile('payment_proof_file')) {
                $storedProofName = basename(
                    $request->file('payment_proof_file')->store('reservation-proofs', 'public')
                );
            }

            Reservation::create([
                'id_evento'            => $validated['id_evento'],
                'id_customer'          => $customer->id,
                'start_time'           => $validated['start_time'],
                'end_time'             => $validated['end_time'],
                'date'                 => $validated['date'],
                'reservation_status'   => 'free',
                'payment_status'       => 'pending',
                'payment_proof_number' => $validated['payment_proof_number'] ?? null,
                'payment_proof_name'   => $storedProofName,
            ]);
        });

        return response()->json([
            'message' => 'Reserva registrada con éxito. Revisaremos tu pago pronto.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        return to_route('reservations.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        return Inertia::render('reservations/edit', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'id_evento' => ['required', 'integer', Rule::exists('eventos', 'id')],
            'id_pay' => ['nullable', 'integer', Rule::exists('pays', 'id')],
            'id_customer' => ['required', 'integer', Rule::exists('customers', 'id')],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'date' => ['required', 'date_format:Y-m-d'],
            'reservation_status' => ['required', 'in:free,busy'],
            'payment_status' => ['required', 'in:pending,approved,rejected'],
            'payment_proof_name' => ['nullable', 'string', 'max:255'],
            'payment_proof_number' => ['nullable', 'integer', 'min:1'],
        ]);

        $reservation->update([
            'id_evento' => $validated['id_evento'],
            'id_pay' => $validated['id_pay'] ?? null,
            'id_customer' => $validated['id_customer'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'date' => $validated['date'],
            'reservation_status' => $validated['reservation_status'],
            'payment_status' => $validated['payment_status'],
            'payment_proof_name' => $validated['payment_proof_name'] ?? null,
            'payment_proof_number' => $validated['payment_proof_number'] ?? null,
        ]);

        return to_route('reservations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return to_route('reservations.index');
    }

    // FUNCION DE MOSTRAR ESTADO:OCUPADO EN EL CALENDARIO
    public function getReservasOcupadas()
    {
        Reservation::expirePending();

        $reservasOcupadas = Reservation::query()
            ->blocking()
            ->with('evento:id,nombre')
            ->select('id', 'id_evento', 'date', 'start_time', 'end_time', 'reservation_status', 'payment_status', 'created_at')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (Reservation $r) => [
                'id'                 => $r->id,
                'id_evento'          => $r->id_evento,
                'event_nombre'       => $r->evento?->nombre,
                'date'               => $r->date,
                'start_time'         => $r->start_time,
                'end_time'           => $r->end_time,
                'reservation_status' => $r->reservation_status,
                'payment_status'     => $r->payment_status,
                'created_at'         => $r->created_at,
            ]);

        return response()->json($reservasOcupadas);
    }

    // FUNCION PARA TRAER EL TIPO DE RESERVA
    public function getTypeEvents()
    {
        try {
            // Sembrar eventos iniciales si la tabla está vacía
            if (Eventos::count() === 0) {
                Eventos::create([
                    'nombre'         => 'Fútbol',
                    'tipo'           => 'cancha',
                    'precio'         => 50,
                    'descripcion'    => 'Reserva de cancha de fútbol',
                    'estado'         => 'free',
                    'tipo_ambiente'  => 'compartido',
                    'ambiente_grupo' => 1,
                ]);

                Eventos::create([
                    'nombre'         => 'Vóley',
                    'tipo'           => 'cancha',
                    'precio'         => 50,
                    'descripcion'    => 'Reserva de cancha de vóley',
                    'estado'         => 'free',
                    'tipo_ambiente'  => 'compartido',
                    'ambiente_grupo' => 1,
                ]);

                Eventos::create([
                    'nombre'         => 'Evento',
                    'tipo'           => 'evento',
                    'precio'         => 0,
                    'descripcion'    => 'Eventos generales con precio variable',
                    'estado'         => 'free',
                    'tipo_ambiente'  => 'propio',
                    'ambiente_grupo' => null,
                ]);
            }

            $typeEvents = Eventos::select('id', 'nombre', 'tipo', 'precio', 'tipo_ambiente', 'ambiente_grupo')->get();

            return response()->json($typeEvents);
        } catch (\Throwable $e) {
            \Log::error('Error in getTypeEvents: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ], 500);
            }

            return response()->json(['error' => 'internal error'], 500);
        }
    }

    // CAPTURAR DATOS DE CLIENTE PARA RESERVA DESDE EL INICIO
    public function postCustomers(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'regex:/^\d{8}$/'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Cliente creado',
            'data' => $customer,
        ], 201);
    }

    public function approvePayment(Reservation $reservation)
    {
        Reservation::expirePending();
        $reservation->refresh();

        if ($reservation->payment_status === 'rejected') {
            return back()->withErrors([
                'approval' => 'La reserva pendiente venció y el horario fue liberado.',
            ]);
        }

        $eventoIdsForApproval = $this->resolveGrupoIds(Eventos::select('id', 'tipo_ambiente', 'ambiente_grupo')->findOrFail($reservation->id_evento));

        $hasConflict = Reservation::query()
            ->where('id', '!=', $reservation->id)
            ->whereIn('id_evento', $eventoIdsForApproval)
            ->where('date', $reservation->date)
            ->blocking()
            ->where('start_time', '<', $reservation->end_time)
            ->where('end_time', '>', $reservation->start_time)
            ->exists();

        if ($hasConflict) {
            return back()->withErrors([
                'approval' => 'No se puede aprobar: ese horario ya fue ocupado por otra reserva.',
            ]);
        }

        $reservation->update([
            'payment_status' => 'approved',
            'payment_reviewed_at' => now(),
            'reservation_status' => 'busy',
        ]);

        return to_route('reservations.index');
    }

    public function rejectPayment(Reservation $reservation)
    {
        $reservation->update([
            'payment_status' => 'rejected',
            'payment_reviewed_at' => now(),
            'reservation_status' => 'free',
        ]);

        return to_route('reservations.index');
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

    private function normalizeCustomerName(string $name, ?string $lastname = null): array
    {
        if (!empty($lastname)) {
            return [trim($name), trim($lastname)];
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];
        if (count($parts) <= 1) {
            return [trim($name), '.'];
        }

        $firstName = array_shift($parts);

        return [$firstName, implode(' ', $parts)];
    }
}

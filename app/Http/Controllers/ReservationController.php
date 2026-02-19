<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Eventos;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservations = Reservation::with(['eventos:id,nombre', 'users:id,name', 'pays:id,amount', 'customers:id,name'])
            ->latest()
            ->get()
            ->map(fn(Reservation $reservation) => [
                'id' => $reservation->id,
                'id_evento' => $reservation->id_evento,
                'id_user' => $reservation->id_user,
                'id_pay' => $reservation->id_pay,
                'id_customer' => $reservation->id_customer,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'date' => $reservation->date,
                'reservation_status' => $reservation->reservation_status,

                'event_name' => $reservation->event?->nombre,
                'user_name' => $reservation->user?->name,
                'pay_amount' => $reservation->pay?->amount,
                'customer_name' => $reservation->customer?->name,
            ]);

        return Inertia::render('reservations/index', [
            'reservations' => $reservations,
            'eventos' => Eventos::select('id', 'nombre', 'precio')->get(),
        ]);

        return  inertia_location('/principal/form-1');
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
        // $validated = $request->validate([
        //     // CREAR RESERVAS EN DASHBOARD
        //     // 'id_evento' => ['required', 'integer', Rule::exists('eventos', 'id')],
        //     // 'id_user' => ['required', 'integer', Rule::exists('users', 'id')],
        //     // 'id_pay' => ['required', 'integer', Rule::exists('pays', 'id')],
        //     // 'id_customer' => ['required', 'integer', Rule::exists('customers', 'id')],
        //     // 'start_time' => ['required', 'date_format:H:i'],
        //     // 'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        //     // 'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        //     // 'reservation_status' => ['required', 'in:free,busy'],

        //     // CREAR RESERVAS EN EL INICIO
        //     'name' => ['required', 'string', 'max:255'],
        //     'lastname'=>['required', 'string', 'max:255'],
        //     'email' => ['required', 'email', 'max:255'],
        //     'phone' => ['required', 'string', 'max:20'],

        //     'id_evento' => ['required', 'integer', Rule::exists('eventos', 'id')],
        //     'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        //     'start_time' => ['required', 'date_format:H:i'],
        // ]);

        // $reservation = Reservation::create([
        //     'id_evento' => $validated['id_evento'],
        //     'id_user' => $validated['id_user'],
        //     'id_pay' => $validated['id_pay'],
        //     'id_customer' => $validated['id_customer'],
        //     'start_time' => $validated['start_time'],
        //     'end_time' => $validated['end_time'],
        //     'date' => $validated['date'],
        //     'reservation_status' => $validated['reservation_status']
        // ]);

        // // return response()->json([
        // //     'message' => 'Reservación completada',
        // //     'data' => $reservation
        // // ], 201);
        // // Reservation::create($validated);
        // // return DB:trasaction(function() use ($request){
        // //     $customer = Customer::updateOrcreate([

        // //     ]);
        // // });
        // return response()->json($reservation);
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
        return to_route('reservations.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'id_evento' => ['required', 'integer', Rule::exists('eventos', 'id')],
            'id_user' => ['required', 'integer', Rule::exists('users', 'id')],
            'id_pay' => ['required', 'integer', Rule::exists('pays', 'id')],
            'id_customer' => ['required', 'integer', Rule::exists('customers', 'id')],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'date' => ['required', 'date_format:Y-m-d'],
            'reservation_status' => ['required', 'in:free,busy'],
        ]);

        $reservation->update([
            'id_evento' => $validated['id_evento'],
            'id_user' => $validated['id_user'],
            'id_pay' => $validated['id_pay'],
            'id_customer' => $validated['id_customer'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'date' => $validated['date'],
            'reservation_status' => $validated['reservation_status']
        ]);

        // return response()->json([
        //     'message' => 'Reservación actualizada',
        //     'data' => $reservation
        // ], 200);
        return to_route('reservations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        // return response()->json([
        //     'message' => 'Reservación eliminada',
        // ], 200);
        return to_route('reservations.index');
    }



    //BOTON DE CAMBIAR ESTADO DE RESERVA
    // public function toggleStatus(Reservation $reservation)
    // {
    //     $reservation->update([
    //         'reservation_status' => $reservation->reservation_status === 'free' ? 'busy' : 'free'
    //     ]);
    //     return to_route('reservations.index');
    // }

    // FUNCION DE MOSTRAR ESTADO:OCUPADO EN EL CALENDARIO
    public function getReservasOcupadas()
    {
        $reservasOcupadas = Reservation::
        where('reservation_status', 'busy')
        ->select('id', 'date', 'start_time', 'end_time')
        ->orderBy('date')
        ->orderBy('start_time')
        ->get();

        $eventos = $reservasOcupadas->map(function ($reserva) {
            return [
                'id' => $reserva->id,
                'date' => $reserva->date,
                'start_time' => $reserva->start_time,
                'end_time' => $reserva->end_time,
            ];
        });
        
         return response()->json($eventos);
    }

    // FUNCION PARA TRAER EL TIPO DE RESERVA
    public function getTypeEvents(){
        $type_events = Eventos::select('id', 'nombre')->get();
        return response()->json($type_events);
    }

    // CAPTURAR DATOS DE CLIENTE PARA RESERVA DESDE EL INICIO
    public function postCustomers(Request $request){
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname'=>['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $customer = Customer::create($validated);
        return response()->json([
            'message' => 'Cliente creado',
            'data' => $customer
        ], 201);
         return response()->json($customer);
    }
}

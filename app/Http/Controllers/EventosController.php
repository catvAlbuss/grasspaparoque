<?php

namespace App\Http\Controllers;

use App\Models\eventos;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Psy\Readline\Hoa\EventSource;

class EventosController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $eventos = eventos::all();

        return Inertia::render('events/index', [
            'eventos' => $eventos,
        ]);
    }
    //resources/js/pages/events/index.vue

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return to_route('events.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([

            'name' => ['required', 'string', 'max:250'],
            'lastname' => ['required', 'string', 'max:250'],
            'email' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'number', 'max:9'],
            'type_reservation' => ['required', 'string', 'max:250'],
            'date'=> ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        Eventos::create([
            'nombre' => $validate['name'],
            'precio' => $validate['precio'],
            'descripcion' => $validate['descripcion'] ?? null,
            'estado' => $validate['estado'],
        ]);

        // $events->syncRoles([$validate['role']]);

        // return to_route('events.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(eventos $eventos)
    {
        return to_route('events.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(eventos $eventos)
    {
        return to_route('events.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $eventosId)
    {
        $events = Eventos::query()->findOrFail($eventosId);

        $validate = $request->validate([
            'nombre' => ['required', 'string', 'max:250'],
            'precio' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['required', 'string', 'max:1000'],
            'estado' => ['required', 'in:Libre,Ocupado'],
        ]);

        $payload = [
            'nombre' => $validate['nombre'],
            'precio' => $validate['precio'],
            'descripcion' => $validate['descripcion'],
            'estado' => $validate['estado'],
        ];

        Log:
        info($payload);
        // if (! empty($validated['password'])) {
        //     $payload['password'] = $validated['password'];
        // }
        $events->update($payload);
        // $events->syncRoles([$validated['role']]);

        return to_route('eventos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Eventos $request, string $eventosId)
    {
        $events = Eventos::query()->findOrFail($eventosId);

        // if ($request->events()?->id === $events->id) {
        //     return back()->withErrors([
        //         'delete' => 'No puedes eliminar tu propio usuario.',
        //     ]);
        // }

        $events->delete();

        // return to_route('events.index');
    }
}

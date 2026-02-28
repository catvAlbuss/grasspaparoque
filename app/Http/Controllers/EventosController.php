<?php

namespace App\Http\Controllers;

use App\Models\eventos;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Validación CORREGIDA para que coincida con tu frontend
        $validate = $request->validate([
            'nombre' => ['required', 'string', 'max:250'],      // ← nombre, no name
            'precio' => ['required', 'numeric', 'min:0'],       // ← precio
            'descripcion' => ['nullable', 'string', 'max:1000'], // ← descripcion
            'estado' => ['required', 'integer', 'min:0'],       // ← estado como número
        ]);

        eventos::create([
            'nombre' => $validate['nombre'],
            'precio' => $validate['precio'],
            'descripcion' => $validate['descripcion'] ?? '',
            'estado' => $validate['estado'],
        // $validate = $request->validate([

        //     'name' => ['required', 'string', 'max:250'],
        //     'lastname' => ['required', 'string', 'max:250'],
        //     'email' => ['required', 'string', 'max:1000'],
        //     'phone' => ['required', 'number', 'max:9'],
        //     'type_reservation' => ['required', 'string', 'max:250'],
        //     'date'=> ['required', 'date'],
        //     'start_time' => ['required', 'date_format:H:i'],
        //     'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        // ]);

        Eventos::create([
            'nombre' => $request->input('nombre'),
            'precio' => $request->input('precio'),
            'descripcion' => $request->input('descripcion') ?? null,
            'estado' => $request->input('estado'),
        ]);

        return to_route('eventos.index'); // ✅ Return agregado
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $eventosId)
    {
        $events = eventos::findOrFail($eventosId);

        // ✅ Validación CORREGIDA para update
        $validate = $request->validate([
            'nombre' => ['required', 'string', 'max:250'],
            'precio' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['required', 'string', 'max:1000'],
            'estado' => ['required', 'integer', 'min:0'], // ← Ahora acepta números
        ]);

        $events->update([
            'nombre' => $validate['nombre'],
            'precio' => $validate['precio'],
            'descripcion' => $validate['descripcion'],
            'estado' => $validate['estado'],
        ]);

        return to_route('eventos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $eventosId)
    {
        $events = eventos::findOrFail($eventosId);
        $events->delete();

        return to_route('eventos.index');
    }
}
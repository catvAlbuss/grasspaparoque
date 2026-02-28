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
        $validate = $request->validate([
            'nombre' => ['required', 'string', 'max:250'],
            'precio' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['required', 'integer', 'min:0'],
        ]);

        eventos::create([
            'nombre' => $validate['nombre'],
            'precio' => $validate['precio'],
            'descripcion' => $validate['descripcion'] ?? '',
            'estado' => $validate['estado'],
        ]);

        return to_route('eventos.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $eventosId)
    {
        $events = eventos::findOrFail($eventosId);

        $validate = $request->validate([
            'nombre' => ['required', 'string', 'max:250'],
            'precio' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['required', 'string', 'max:1000'],
            'estado' => ['required', 'integer', 'min:0'],
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

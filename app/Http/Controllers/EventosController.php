<?php

namespace App\Http\Controllers;

use App\Models\eventos;
use App\Http\Controllers\Controller;
use App\Models\User;
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(eventos $eventos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(eventos $eventos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, eventos $eventos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(eventos $eventos)
    {
        //
    }
}

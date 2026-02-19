<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;


class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::all();
        return Inertia::render('products/index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * Ingresar datos
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'stock' => ['required','string', 'max:255'],
            'price_unit' => ['required', 'string'],
            'price_higher' => ['required', 'string'],
            'expiration_date' => ['required', 'string'],
        ]);

        $products = Products::create([
            'name' => $validated['name'],
            'description'=>$validated['description'],
            'stock' => $validated['stock'],
            'price_unit' => $validated['price_unit'],
            'price_higher' => $validated['price_higher'],
            'expiration_date' => $validated['expiration_date'],
        ]);

        return to_route('products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Products $products)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * Actualizar Datos
     */
    public function update(Request $request, string $products_id)
    {
        //
        $prod = Products::query()->findOrFail($products_id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'stock' => ['required','string', 'max:255'],
            'price_unit' => ['required', 'string'],
            'price_higher' => ['required', 'string'],
            'expiration_date' => ['required', 'string'],
        ]);

        $payLoad =[

            'name' => $validated['name'],
            'description' => $validated['description'],
            'stock' => $validated['stock'],
            'price_unit' => $validated['price_unit'],
            'price_higher' => $validated['price_higher'],
            'expiration_date' => $validated['expiration_date']
        ];

        $prod -> update($payLoad);
        return to_route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     * Eliminar un producto
     */
    public function destroy(string $products_id)
    {
        //
        $prod = Products::query()->findOrFail($products_id);

        $prod ->delete();
        return to_route('products.index');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Http\Controllers\Controller;
use App\Models\Pay;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

use function Symfony\Component\Clock\now;

class BoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::all();
        return Inertia::render('box/index', [
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
     */
    public function store(Request $request)
    {
        //Log::info('Respuesta: ',$request->all());
        //Ahi se registra las funcionalidades
        /*$validate =  $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.total' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paymentStatus' => 'required|string|in:paid,pending,annulled',
            'paymentMethod' => 'required|string|in:cash,yape,plin,card',
        ]);*/

        DB::beginTransaction();

        try {
            $id_reservations = null;
            $pay_id = null;
            //Log::info('Datos validados:', $validate);
            foreach ($request['items'] as $item) {
                # code
                //Log::info('Datos validados:', $item);
                $pay = Pay::create([
                    'id_products' => $item['id'],
                    'amount' => $item['total'],
                    'payment_status' => $item['status'],
                    'payment_method' => $item['method'],
                    'payment_date' => now(),
                ]);

                $pay_id = $pay->id;

                // Log::info('Guardando Pay:', [
                //     'id_products' => $item['id'],
                //     'amount' => $item['total'],
                //     'payment_status' => $item['paymentStatus'],
                //     'payment_method' => $item['paymentMethod']
                // ]);

                //menorar el stock
                $product = Products::find($item['id']);
                $product->stock = $product->stock - $item['quantity'];
                $product->save();
            }

            Box::create([
                'id_pay' => $pay_id,
                'id_reservations' => $id_reservations,
                'amount' => $request['total'],
                'date_time' => now()
            ]);

            DB::commit();

            return to_route('box.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error:', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Box $box)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Box $box)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Box $box)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Box $box)
    {
        //
    }
}

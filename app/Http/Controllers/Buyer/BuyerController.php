<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerController extends ApiController
{
    /**
     * Muestra una lista de todos los recursos.
     */
    public function index()
    {
        $compradores = Buyer::has('transactions')->get();
        //return response()->json(['Data' => $compradores], 200);
        return $this->showAll($compradores);
    }

    /**
     * Muestra un recurso en especifico.
     */
    public function show(string $id)
    {
        $comprador = Buyer::has('transactions')->findOrFail($id);
        return response()->json(['Data' => $comprador], 200);
    }
}

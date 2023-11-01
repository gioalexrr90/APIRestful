<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Models\Seller;
use Illuminate\Http\Request;

class SellerController extends ApiController
{
    /**
     * Muestra una lista de todos los recursos.
     */
    public function index()
    {
        $vendedores = Seller::has('products')->get();

        //return response()->json(['Data' => $vendedores], 200);
        return $this->showAll($vendedores);
    }

    /**
     * Muestra un recurso en especifico.
     */
    public function show(string $id)
    {
        $vendedor = Seller::has('products')->findOrFail($id);

        return response()->json(['Data' => $vendedor], 200);
    }
}

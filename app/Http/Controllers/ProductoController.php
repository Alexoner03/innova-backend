<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    function listAll() {

        return response()->json(
            Producto::select("id","producto", "p_especial", "stock_real", "id", "cant_caja", "codigo")
                ->where("activo", "SI")
                ->get()
        );
    }

    function filter(Request $request) {
        $validated = $request->validate([
            "value" => "string"
        ]);


        return response()->json(
            Producto::select("id","producto", "p_especial", "stock_real", "id", "cant_caja", "codigo")
                ->where("activo", "SI")
                ->where("producto", "like", "%".$validated["value"]."%")
                ->take(12)
                ->get());
    }
}

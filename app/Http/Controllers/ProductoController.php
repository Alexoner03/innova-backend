<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    function listAll()
    {

        return response()->json(
            Producto::select("id", "producto", "p_especial", "p_unidad", "p_promotor", "stock_real", "id", "cant_caja", "codigo")
                ->where("activo", "SI")
                ->get()
        );
    }

    function filter(Request $request)
    {
        $validated = $request->validate([
            "value" => "string"
        ]);

        $splitted = explode(" ", $validated["value"]);

        $query = Producto::select("id", "producto", "p_especial", "p_unidad", "p_promotor" , "stock_real", "id", "cant_caja", "codigo")
            ->where("activo", "SI");

        $query->where(function ($subQuery) use ($splitted) {
            foreach ($splitted as $word) {
                $subQuery->orWhere("producto", "like", "%" . $word . "%");
            }
        });


        return response()->json(
            $query
                ->orderBy("producto", "ASC")
                ->get());
    }
}

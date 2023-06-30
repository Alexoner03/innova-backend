<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    function listAll()
    {

        return response()->json(
            Producto::select("id", "producto", "p_especial", "p_unidad", "p_promotor", "stock_real", "id", "cant_caja", "codigo", "marca")
                ->where("activo", "SI")
                ->orderBy("producto", "asc")
                ->get()
        );
    }

    function filter(Request $request)
    {
        $validated = $request->validate([
            "value" => "string"
        ]);

        $splitted = explode(" ", $validated["value"]);

        $query = Producto::select("id", "producto", "p_especial", "p_unidad", "p_promotor" , "stock_real", "id", "cant_caja", "codigo", "marca")
            ->where("activo", "SI");

        $last_word = array_pop($splitted);

        foreach ($splitted as $word) {
            $query->where("producto", "like", "%" . $word . "%");
        }

        $query->where(function ($qry) use ($last_word) {
            $qry->where("producto", "like", "%" . $last_word . "%")
                ->orWhere( "marca", "like", "%" . $last_word . "%");
        });

        return response()->json(
            $query
                ->orderBy("producto", "ASC")
                ->get());
    }
}

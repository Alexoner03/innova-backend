<?php

namespace App\Http\Controllers;

use App\Models\Boleta;
use App\Models\BoletaElectronica;
use App\Models\BoletaElectronica2;
use App\Models\Factura;
use App\Models\FacturaElectronica;
use App\Models\FacturaElectronica2;
use App\Models\NotaPedido;
use App\Models\NotaPedido2;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    function listAll()
    {

        return response()->json(
            Producto::select("id", "producto", "p_especial", "p_unidad", "p_promotor", "stock_real", "id", "cant_caja", "codigo", "marca", "p_compra")
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

        $query = Producto::select("id", "producto", "p_especial", "p_unidad", "p_promotor" , "stock_real", "id", "cant_caja", "codigo", "marca", "p_compra")
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

    public function history(Request $request) {

        $fields = $request->validate([
           "serie" => 'string|required'
        ]);



    }
}

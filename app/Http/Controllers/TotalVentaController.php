<?php

namespace App\Http\Controllers;

use App\Models\TotalPedido;
use App\Models\TotalVenta;
use Illuminate\Database\Grammar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TotalVentaController extends Controller
{
    public function index(Request $request)
    {

        $fields = $request->validate([
            'cliente' => 'string|nullable|min:3'
        ]);

        $cliente = $fields["cliente"] ?? null;

        $query = TotalVenta::where("credito", "CREDITO")->where("entregado", "SI");
        $user = auth()->user();
        if ($user && $user->cargo !== "ADMIN") {
            $query = $query->where("vendedor", auth()->user()->nombre);
        }

        if (!!$cliente && $cliente !== "") {
            $query = $query->where("cliente", 'like', "%$cliente%");
        }

        return response()->json($query->select('cliente', 'fecha', 'fechapago', 'vendedor', 'total', 'pendiente', 'acuenta', 'serieventas')->get());
    }

    function store(Request $request) {
/*        $validated = $request->validate([
            "comment" =>     "string|nullable",
            "client" =>      "string|required",
            "products" =>    "required|array|min:1",
            "products.*.cant" => "numeric|required",
            "products.*.id" => "numeric|required",
            "products.*.price" => "numeric|required",
        ]);*/
        /*DB::raw("SELECT MAX(seriepedido) FROM total_pedido");*/
        $lastSeriePedido = DB::table("total_pedido")
            ->select(DB::raw("MAX(seriepedido) as max"))
            ->get();

        $last = intval($lastSeriePedido[0]->max) + 1;
        $next = str_pad($last, 7,"0000000", STR_PAD_LEFT);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\TotalVenta;
use Illuminate\Http\Request;

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
}

<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    function filter(Request $request) {
        $validated = $request->validate([
           "type" => "string",
           "value" => "string"
        ]);

        if($validated["type"] === "ruc") {
            return response()->json(
                Cliente::select('id_cliente', 'cliente', 'direccion', 'ruc')
                    ->where("ruc", "like", "%".$validated["value"]."%")
                    ->where("tipo", "FERRETERIA")
                    ->where("activo", "<>", "ANULADO")
                    ->take(10)
                    ->get()
            );
        }

        return response()->json(
            Cliente::select('id_cliente', 'cliente', 'direccion', 'ruc')
                ->where("cliente", "like", "%".$validated["value"]."%")
                ->where("tipo", "FERRETERIA")
                ->where("activo", "<>", "ANULADO")
                ->take(10)
                ->get());
    }

    function listAll(Request $request) {
        return response()->json(
            Cliente::select('id_cliente', 'cliente', 'direccion', 'ruc')
                ->where("tipo", "FERRETERIA")
                ->where("activo", "<>", "ANULADO")
                ->get()
        );
    }
}

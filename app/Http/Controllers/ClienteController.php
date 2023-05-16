<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

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

    function store(Request $request) {

        $validated = $request->validate([
            "ruc" =>            "required|string",
            "cliente" =>        "required|string",
            "direccion" =>      "required|string",
            "telefono" =>       "required|string",
            "representante" =>  "required|string",
        ]);

        try {

            $findClient = Cliente::where('ruc', $validated['ruc'])->first();

            if($findClient !== null) {
                return response()->json([
                    "result" => false,
                    "message" => "El cliente ya está registrado",
                    "client" => -1
                ],422);
            }


            $cliente = new Cliente();

            $cliente->ruc = $validated["ruc"];
            $cliente->cliente = $validated["cliente"];
            $cliente->direccion = $validated["direccion"];
            $cliente->celular = $validated["telefono"];
            $cliente->representante = $validated["representante"];
            $cliente->tipo = "FERRETERIA";
            $cliente->activo = "SI";

            $cliente->save();
            $cliente->refresh();

            return response()->json([
                "result" => true,
                "message" => "Cliente guardado",
                "client" => $cliente->id_cliente
            ]);
        }catch (\Exception $e)
        {
            return response()->json([
                "result" => false,
                "message" => "Error guardando usuario: " . $e->getMessage(),
                "client" => -1
            ],400);
        }
    }
}

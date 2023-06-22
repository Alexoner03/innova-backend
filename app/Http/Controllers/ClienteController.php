<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    function filter(Request $request)
    {
        $validated = $request->validate([
           "type" => "string",
           "value" => "string"
        ]);

        if($validated["type"] === "ruc") {
            return response()->json(
                Cliente::select('id_cliente', 'cliente', 'direccion', 'ruc')
                    ->where("ruc", "like", "%".$validated["value"]."%")
                    ->where("activo", "<>", "ANULADO")
                    ->orderBy('cliente', "asc")
                    ->get()
            );
        }

        $splitted = explode(" ", $validated["value"]);

        $query = Cliente::select('id_cliente', 'cliente', 'direccion', 'ruc')
                    ->where("activo", "<>", "ANULADO");

        foreach ($splitted as $word) {
            $query->where("cliente", "like", "%" . $word . "%");
        }

        return response()->json($query->orderBy('cliente', "asc")->get());
    }

    function listAll(Request $request) {
        return response()->json(
            Cliente::select('id_cliente', 'cliente', 'direccion', 'ruc')
                ->where("activo", "<>", "ANULADO")
                ->orderBy('cliente', "asc")
                ->get()
        );
    }

    function store(Request $request)
    {
        $validated = $request->validate([
            "ruc" =>            "required|string",
            "cliente" =>        "required|string",
            "direccion" =>      "required|string",
            "telefono" =>       "required|string",
            "vendedor" =>  "required|string",
            "correo" => "required|email",
            "nombre_cliente" =>  "required|string",
            "zona" =>  "required|string",
            "nombre_comercial" =>  "required|string"
        ]);

        try
        {
            $findClient = Cliente::where('ruc', $validated['ruc'])->first();

            if($findClient !== null) {
                return response()->json([
                    "result" => false,
                    "message" => "El cliente ya está registrado",
                    "client" => -1
                ],422);
            }

            $cliente = new Cliente();

            $cliente->ruc = str($validated["ruc"])->upper();
            $cliente->cliente = str($validated["cliente"])->upper();
            $cliente->direccion = str($validated["direccion"])->upper();
            $cliente->celular = str($validated["telefono"])->upper();
            $cliente->representante = str($validated["vendedor"])->upper();
            $cliente->tipo = "FERRETERIA";
            $cliente->activo = "SI";
            $cliente->correo = str($validated["correo"])->upper();
            $cliente->nombrecliente = str($validated["nombre_cliente"])->upper();
            $cliente->zona = str($validated["zona"])->upper();
            $cliente->nomcomercial = str($validated["nombre_comercial"])->upper();
            $cliente->fcreacion = Carbon::now()->format('Y-m-d');

            if($request->fecha_nacimiento) $cliente->fnacimiento = $request->fecha_nacimiento;

            $cliente->save();
            $cliente->refresh();

            return response()->json([
                "result" => true,
                "message" => "Cliente guardado",
                "client" => $cliente->id_cliente
            ]);
        }
        catch (\Exception $e)
        {
            return response()->json([
                "result" => false,
                "message" => "Error guardando usuario: " . $e->getMessage(),
                "client" => -1
            ], 400);
        }
    }
}

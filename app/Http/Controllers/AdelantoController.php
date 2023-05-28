<?php

namespace App\Http\Controllers;

use App\Models\ACuenta;
use App\Models\Adelanto;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdelantoController extends Controller
{
    private readonly Connection $connection;

    public function __construct()
    {
        $connectionLabel = request()->has("db") ? request()->get("db") : auth()->payload()->get('BASE');
        $this->connection = DB::connection($connectionLabel);
    }

    public function findBySerie(Request $request)
    {
        $validated = $request->validate([
            'serie' => 'string|min:3'
        ]);

        /*SELECT fecha,adelanto,encargado FROM adelantos WHERE serie='".$_REQUEST['serie']."'";*/
        $results = Adelanto::where('serie', $validated['serie'])->select('fecha', 'adelanto', 'encargado')->get();

        return response()->json($results);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            "adelantos" =>              "array|required|min:1",
            "adelantos.*.pendiente" =>  "required|numeric|min:0",
            "adelantos.*.acuenta" =>    "required|numeric|min:0",
            "adelantos.*.cliente" =>    "required|string",
            "adelantos.*.serie" =>      "required|string",
            "adelantos.*.documento" => "required|string"
        ]);

        $this->connection->beginTransaction();

        try {
            foreach ($validated["adelantos"] as $adelanto)
            {

                $acuenta = new ACuenta();
                $acuenta->serie = $adelanto["serie"];
                $acuenta->vendedor = auth()->user()->nombre;
                $acuenta->cliente = $adelanto["cliente"];
                $acuenta->monto = $adelanto["acuenta"];
                $acuenta->fecha = date("Y-m-d");
                $acuenta->pendiente = "SI";
                $acuenta->documento = $adelanto["documento"];

                $acuenta->save();
            }
            $this->connection->commit();
            return response()->json([
                "result" => true,
            ]);
        }catch (\Exception $e){
            $this->connection->rollBack();
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $e->getMessage()
            ], 422);
        }
    }
}

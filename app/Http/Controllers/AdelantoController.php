<?php

namespace App\Http\Controllers;

use App\Models\ACuenta;
use App\Models\Adelanto;
use App\Models\Cliente;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Twilio\Exceptions\ConfigurationException;

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

    public function notifyAddvacement(Request $request) {
        $validated = $request->validate([
            "firma" => "required|string",
            "pagante" => "required|string",
            "cliente" => "required|string"
        ]);

//        $cliente = Cliente::where("cliente", $validated["cliente"])->first();
//
//        if($cliente == null || $cliente->celular == null || $cliente->celular == "") {
//            return response()->json([
//                "result" => false,
//                "message" => "Cliente no tiene numero de contacto"
//            ], 422);
//        }

        $signName = $this->storeImage($validated["firma"]);
        $buyer = $validated["pagante"];

        $message = "Se ha registrado un nuevo adelanto con éxito, le adjuntamos la imagen con la firma. ";

        if ($buyer !== "") {
            $message .=  $buyer ." realizó el pago.";
        }

        $message .= " muchas gracias. Atte: " . auth()->user()->nombre;

        Http::post("http://190.117.60.67:3001/lead", [
            "message"   => $message,
            "phone" => "51960536426",
            "media" => url("/storage/" . $signName)
        ]);

        return response()->json([
            "result" => true,
            "message" => "ok",
            "file" => $signName
        ]);
    }

    public function deleteAdvacement(Request $request) {
        $validated = $request->validate([
            "firma" => "required|string",
            "pagante" => "required|string",
            "monto" => "required|numeric"
        ]);

        $cliente = "Alexander";
//        $cliente = Cliente::where("cliente", $validated["cliente"])->first();
//
//        if($cliente == null || $cliente->celular == null || $cliente->celular == "") {
//            return response()->json([
//                "result" => false,
//                "message" => "Cliente no tiene numero de contacto"
//            ], 422);
//        }

        $file = public_path('storage/'.$validated["firma"]);

        if(file_exists($file)){
            unlink($file);
        }else {
            return response()->json([
                "result" => false,
                "file" => "Firma no encontrada"
            ]);
        }

        Http::post("http://190.117.60.67:3001/lead", [
            "message"   => "{$cliente}, Su ultimo adelanto por S/. {$validated["monto"]} ha sido cancelado",
            "phone" => "51960536426",
        ]);

        return response()->json([
            "result" => true,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "adelantos" => "array|required|min:1",
            "adelantos.*.pendiente" => "required|numeric|min:0",
            "adelantos.*.acuenta" => "required|numeric|min:0",
            "adelantos.*.cliente" => "required|string",
            "adelantos.*.serie" => "required|string",
            "adelantos.*.documento" => "required|string",
            "adelantos.*.firma" => "required|string",
            "adelantos.*.pagante" => "string|nullable",
        ]);

        $this->connection->beginTransaction();

        try {
            foreach ($validated["adelantos"] as $adelanto) {
                $acuenta = new ACuenta();
                $acuenta->serie = $adelanto["serie"];
                $acuenta->vendedor = auth()->user()->nombre;
                $acuenta->cliente = $adelanto["cliente"];
                $acuenta->monto = $adelanto["acuenta"];
                $acuenta->fecha = date("Y-m-d");
                $acuenta->pendiente = "SI";
                $acuenta->documento = $adelanto["documento"];
                $acuenta->firma = $adelanto["firma"];
                $acuenta->pagante = $adelanto["pagante"] ?? "";

                $acuenta->save();
            }
            $this->connection->commit();
            return response()->json([
                "result" => true,
            ]);

        } catch (\Exception $e) {
            $this->connection->rollBack();
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $e->getMessage()
            ], 422);
        }
    }

    private function storeImage(string $image_64): string
    {
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

        // find substring from replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $image_64);

        $image = str_replace(' ', '+', $image);
        $datetime = date('d_m_Y_H_i_s');
        $imageName = str()->random(10) . '_' . $datetime . '.' . $extension;
        Storage::disk('public')->put($imageName, base64_decode($image));
        return $imageName;
    }
}

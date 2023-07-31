<?php

namespace App\Http\Controllers;

use App\Models\ACuenta;
use App\Models\Adelanto;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;

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
            "adelantos.*.documento" =>  "required|string",
            "adelantos.*.firma" =>      "required|string",
            "adelantos.*.pagante" =>    "string|nullable",
        ]);

        $this->connection->beginTransaction();

        try {
            foreach ($validated["adelantos"] as $adelanto)
            {
                $signName = $this->storeImage($adelanto["firma"]);

                $acuenta = new ACuenta();
                $acuenta->serie = $adelanto["serie"];
                $acuenta->vendedor = auth()->user()->nombre;
                $acuenta->cliente = $adelanto["cliente"];
                $acuenta->monto = $adelanto["acuenta"];
                $acuenta->fecha = date("Y-m-d");
                $acuenta->pendiente = "SI";
                $acuenta->documento = $adelanto["documento"];
                $acuenta->firma = $signName;
                $acuenta->pagante = $adelanto["pagante"] ?? "";

                $acuenta->save();
            }
            $this->connection->commit();
            $this->notifyOrders($validated["adelantos"]);
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

    private function storeImage(String $image_64): string
    {
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

        $replace = substr($image_64, 0, strpos($image_64, ',')+1);

        // find substring from replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $image_64);

        $image = str_replace(' ', '+', $image);
        $datetime = date('d_m_Y_H_i_s');
        $imageName = str()->random(10).'_'.$datetime.'.'.$extension;
        Storage::disk('public')->put($imageName, base64_decode($image));
        return $imageName;
    }

    /**
     * @throws ConfigurationException
     */
    private function notifyOrders(array $advacements): void {

        $sid =  env('TWILIO_AUTH_SID');
        $token =  env('TWILIO_AUTH_TOKEN');
        $from =  env('TWILIO_WHATSAPP_FROM');

        $twilio = new Client($sid, $token);

        foreach ($advacements as $advacement){
            $message = $twilio->messages
                ->create("whatsapp:+51960536426",
                    array(
                        "from" => $from,
                        "body" => "Tu adelanto de {$advacement['acuenta']} por el pedido {$advacement['serie']} ha sido guardado"
                    ));
        }
    }
}

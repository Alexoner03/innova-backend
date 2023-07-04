<?php

namespace App\Http\Controllers;

use App\Models\Boleta;
use App\Models\BoletaElectronica;
use App\Models\BoletaElectronica2;
use App\Models\Cliente;
use App\Models\Devolucion;
use App\Models\Factura;
use App\Models\FacturaElectronica;
use App\Models\FacturaElectronica2;
use App\Models\NotaPedido;
use App\Models\NotaPedido2;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\TotalPedido;
use App\Models\TotalVenta;
use Illuminate\Database\Connection;
use Illuminate\Database\Grammar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Luecano\NumeroALetras\NumeroALetras;
use PDF;

class TotalVentaController extends Controller
{
    private readonly Connection $connection;
    private readonly array $config;
    public function __construct()
    {
        $connectionLabel = request()->has("db") ? request()->get("db") : auth()->payload()->get('BASE');
        $this->connection = DB::connection($connectionLabel);
        $options = [
            'innovaprincipal' => [
                'logo1' => 'logo_innova.png',
                'ruc1' => 'RUC: 20487211410',
                'razon1' => 'INVERSIONES E IMPORTACIONES FERREBOOM S.R.L.',
                'direccion1' => 'PROL. HUANUCO N° 258-A - HUANCAYO - HUANCAYO - JUNIN',
                'direccion2' => 'PRINCIPAL: PROL. HUANUCO N° 258-A - HUANCAYO - HUANCAYO - JUNIN SUCURSAL: PROL. HUANUCO N° 272 - HUANCAYO - HUANCAYO - JUNIN',
                'serie1' => '001',
                'serie2' => '002',
                'datos1' => 'CEL: 943322258 - 939747012 CORREO: innova.t1.huancayo@gmail.com',
            ],
            'castilla' => [
                'logo1' => 'logo_innova.png',
                'ruc1' => 'RUC: 20601765641',
                'razon1' => 'GRUPO FERRETERO INNOVA S.R.L.',
                'direccion1' => 'AV. MARISCAL CASTILLA NRO. 1704 URB. LAMBLASPATA - EL TAMBO - HUANCAYO - JUNIN',
                'serie1' => '004',
                'datos1' => 'CEL: 950543772 CORREO: innova.eltambo@gmail.com',
            ],
            'castilla2' => [
                'logo1' => 'logo_innova.png',
                'ruc1' => 'RUC: 20601765641',
                'razon1' => 'GRUPO FERRETERO INNOVA S.R.L.',
                'direccion1' => 'AV. MARISCAL CASTILLA NRO. 2075 URB. LAMBLASPATA - EL TAMBO - HUANCAYO - JUNIN',
                'serie1' => '006',
                'datos1' => 'CEL: 950543772 CORREO: innova.eltambo@gmail.com',
            ],
            'jauja' => [
                'logo1' => 'logo_innova.png',
                'ruc1' => 'RUC: 20609257076',
                'razon1' => 'INVERSIONES FERRETERA INNOVA S.A.C.',
                'direccion1' => 'AV. RICARDO PALMA N° 251 - JAUJA - JAUJA - JUNIN',
                'serie1' => '001',
                'datos1' => 'CEL: 924681501 CORREO: innovajauja251@gmail.com',
            ],
            'almacenayacucho' => [
                'logo1' => 'logo_ferreboom.jpg',
                'ruc1' => 'RUC: 20609697041',
                'razon1' => 'FERREBOOM AYACUCHO S.A.C.',
                'direccion1' => 'AV. JAVIER PEREZ DE CUELLAR S/N - AYACUCHO - HUAMANGA - AYACUCHO',
                'serie1' => '001',
                'datos1' => 'CEL: 901143148 - 970583609 BCP: CUENTA CORRIENTE 220-3661490-0-68',
            ],
            'ferreboomlima' => [
                'logo1' => 'logo_ferreboom.jpg',
                'ruc1' => 'RUC: 20610184236',
                'razon1' => 'DISTRIBUIDORA FERREBOOM S.A.C.',
                'direccion1' => 'CAL.CHIAPPE LUIS NRO. 643 OTR. CERCADO LIMA - LIMA - LA VICTORIA',
                'serie1' => '001',
                'datos1' => 'CEL: 976525333 BCP: CUENTA CORRIENTE ',
            ],
            'vanidositos' => [
                'logo1' => 'logo_vanidositos.jpeg',
                'ruc1' => 'RUC: 10443630088',
                'razon1' => 'MELGAR POVEZ PAOLA ANGELINA',
                'direccion1' => 'JR. TOMAS GUIDO N° 509 INT. 1A - HUANCAYO - HUANCAYO - JUNIN',
                'serie1' => '001',
                'datos1' => 'CEL: 949978487 - 999050145',
            ],
            'vanidositosbebom' => [
                'logo1' => 'logo_vanidositos.jpeg',
                'ruc1' => 'RUC: 20610504613',
                'razon1' => 'VANIDOSITOS MODA INFANTIL S.A.C.',
                'direccion1' => 'JR. TOMAS GUIDO NRO. 509 HUANCAYO CERCADO JUNIN - HUANCAYO - HUANCAYO',
                'serie1' => '001',
                'datos1' => 'CEL: 949978487 - 999050145',
            ],
            'pichari' => [
                'logo1' => 'logo_clamel.jpeg',
                'ruc1' => 'RUC: 20608596136',
                'razon1' => 'DISTRIBUIDORA FERRETERA CL & M S.A.C.',
                'direccion1' => 'CASTAÑA NRO. 156 - CUSCO - LA CONVENCION - PICHARI',
                'serie1' => '001',
                'datos1' => 'CEL: 925828845 CORREO: ferre.clamel@gmail.com',
            ],
            'tingomaria' => [
                'logo1' => 'logo_innova.png',
                'ruc1' => 'RUC: 10198351054',
                'razon1' => 'DORIS POVEZ SOTO',
                'direccion1' => 'AV. ANTONIO RAIMONDI N° 513 - RUPA - RUPA - LEONCIO PRADO - HUANUCO',
                'serie1' => '001',
                'datos1' => 'CEL: 986872868 CORREO: innova.tingomaria1@gmail.com',
            ],
            'chupaca' => [
                'logo1' => 'logo_innova.png',
                'ruc1' => 'RUC: 20601488567',
                'razon1' => 'CENTRO COMERCIAL FERRETERO LA PRINCIPAL S.R.L.',
                'direccion1' => 'JR. RUFINO ECHENIQUE NRO. 543 P.J. CHUPACA JUNIN - CHUPACA - CHUPACA',
                'serie1' => '001',
                'datos1' => 'CEL: 945793616',
            ],
        ];
        $this->config = array_key_exists($connectionLabel, $options) ? $options[$connectionLabel] : $options["innovaprincipal"];
    }

    public function indexSells(Request $request) {
        $fields = $request->validate([
            'cliente' => 'string|required|min:3',
            'from' => 'string|required',
            'to' => 'string|required',
        ]);

        return TotalVenta::whereIn("credito", ["CREDITO", "CANCELADO", "CONTADO"])
            ->where("entregado", "SI")
            ->where("cliente", $fields["cliente"])
            ->whereBetween("fecha", [$fields["from"],$fields["to"]])
            ->whereIn("documento", [
//                "FACTURA",
                "FACTURA ELECTRONICA",
                "FACTURA ELECTRONICA 2",
//                "BOLETA DE VENTA",
                "BOLETA ELECTRONICA",
                "BOLETA ELECTRONICA 2",
                "NOTA DE PEDIDO",
                "NOTA DE PEDIDO 2",
            ])
            ->where("cliente", "<>", "Devolución")
            ->select('cliente', 'fecha', 'fechapago', 'vendedor', 'total', 'pendiente', 'acuenta', 'serieventas', 'documento')
            ->orderBy('fecha', "DESC")
            ->get();

    }

    public function index(Request $request)
    {

        $fields = $request->validate([
            'cliente' => 'string|nullable|min:3',
        ]);

        $cliente = $fields["cliente"] ?? null;


        $query = TotalVenta::where("credito", "CREDITO")->where("entregado", "SI");


        $user = auth()->user();

        if ($user && $user->cargo !== "ADMIN") {
            $query = $query->where("vendedor", auth()->user()->nombre);
        }

        if (!!$cliente && $cliente !== "")
        {
            $splitted = explode(" ", $cliente);

            foreach ($splitted as $word) {
                $query->where("cliente", "like", "%" . $word . "%");
            }
        }

        return response()->json($query
            ->select('cliente', 'fecha', 'fechapago', 'vendedor', 'total', 'pendiente', 'acuenta', 'serieventas', 'documento')
            ->orderBy('fecha', "DESC")
            ->get()
        );

    }

    public function listDetail(Request $request)
    {
        $fields = $request->validate([
            'serie' => 'string'
        ]);

        $details = NotaPedido::where('serienota', $fields['serie'])->get();
        $details2 = Devolucion::where('seriedevolucion', $fields['serie'])->get();

        $result = [];

        foreach ($details as $item) {
            $result[] = [
                "cantidad" =>   $item->cantidad,
                "producto" =>   $item->producto,
                "unitario" =>   $item->unitario,
                "importe" =>    $item->importe,
                "id" =>         $item->id,
                "estado" =>     "normal",
            ];
        }

        foreach ($details2 as $item) {
            $result[] = [
                "cantidad" =>   $item->cantidad,
                "producto" =>   $item->producto,
                "unitario" =>   $item->unitario,
                "importe" =>    $item->importe,
                "id" =>         $item->id,
                "estado" =>     "devolucion",
            ];
        }

        return response()->json($result);
    }

    public function reporte(Request $request)
    {

        $fields = $request->validate([
           'serie' => 'string|required',
           'tipo' => 'string|required'
        ]);

        $imagePath = public_path($this->config["logo1"]);
        $image = "data:image/png;base64,".base64_encode(file_get_contents($imagePath));
        $registers = $this->getRegister($fields["tipo"],$fields["serie"]);
        $model = TotalVenta::where('serieventas',$fields["serie"])->where('documento', $fields["tipo"])->first();
        $total = $registers->reduce(fn($carry, $item) => $carry + $item->importe, 0);
        $formatter = new NumeroALetras();
        $qrdata = $this->constructQrlabel($model, $total);

        $pdf = PDF::loadView('reporte',[
            'logo' => $image,
            'config' => $this->config,
            'model' => $model,
            'registers' =>$registers,
            'total' => $total,
            'resumen' => $formatter->toMoney($total, 2, 'NUEVOS SOLES', 'CENTIMOS'),
            'qrdata' => $qrdata
        ]);

        return $pdf->stream();
    }

    function store(Request $request)
    {

        $validated = $request->validate([
            "comment" => "string|nullable",
            "client" => "numeric|required",
            "products" => "required|array|min:1",
            "products.*.cant" => "numeric|required",
            "products.*.id" => "numeric|required",
            "products.*.price" => "numeric|required",
        ]);

        //usuario autenticado
        $user = auth()->user();

        //ultima serie de pedido
        $lastSeriePedido = $this->connection->table("total_pedido")
            ->select($this->connection->raw("MAX(seriepedido) as max"))
            ->get();
        $last = intval($lastSeriePedido[0]->max) + 1;
        $next_serie = str_pad($last, 7, "0000000", STR_PAD_LEFT); //nueva serie a crear

        //buscando siguiente numero de pedido
        $lastTotalPedido = TotalPedido::where('seriepedido', $lastSeriePedido[0]->max)->first();
        $nro_pedido = $lastTotalPedido->fecha === date('Y-m-d') ? $lastTotalPedido->nropedido + 1 : 1; //esto debería ser un autoincremente pero bueno...

        $client = Cliente::where('id_cliente', $validated["client"])->first();

        //total de la venta
        $total = collect($validated["products"])->reduce(function (int $curr, $item) {
            return $curr + ($item["price"] * $item["cant"]);
        }, 0);


        $this->connection->beginTransaction();

        try {
            $totalPedido = new TotalPedido();

            $totalPedido->nropedido = $nro_pedido;
            $totalPedido->fecha = date('Y-m-d');
            $totalPedido->hora = date('H:i:s');
            $totalPedido->seriepedido = $next_serie;
            $totalPedido->ruc = $client->ruc;
            $totalPedido->cliente = $client->cliente;
            $totalPedido->direccion = $client->direccion;
            $totalPedido->entregado = 'NO';
            $totalPedido->subtotal = $total;
            $totalPedido->devolucion = "0.00";
            $totalPedido->total = $total;
            $totalPedido->vendedor = $user->nombre;
            $totalPedido->comentario = $validated["comment"] ?? "";
            $totalPedido->credito = $client->credito;

            $totalPedido->save();

            foreach ($validated["products"] as $product) {
                $productRegister = Producto::where("id", $product["id"])->first();

                $pedido = new Pedido();
                $pedido->seriepedido = $next_serie;
                $pedido->id = $product["id"];
                $pedido->compra = $productRegister->p_compra;
                $pedido->producto = $productRegister->producto . " " . $productRegister->marca;
                $pedido->cantidad = $product["cant"];
                $pedido->unitario = $product["price"];
                $pedido->importe = $product["cant"] * $product["price"];
                $pedido->especial = $productRegister->p_promotor;
                $pedido->ruc = $client->ruc;
                $pedido->cliente = $client->cliente;
                $pedido->direccion = $client->direccion;
                $pedido->fecha = date('Y-m-d');
                $pedido->hora = date('H:i:s');
                $pedido->nropedido = $nro_pedido;
                $pedido->vendedor = $user->nombre;

                $pedido->save();
            }

            $this->connection->commit();

            return response()->json([
                "result" => true,
                "message" => $next_serie
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            $this->connection->rollBack();
            return response()->json([
                "result" => false,
                "message" => ""
            ], 422);
        }

    }

    function recordClient(Request $request) {
        $validated = $request->validate([
            "cliente" =>    "required|string",
            "year" =>       "required|numeric|digits:4|min:2015",
        ]);

        $months = ["ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SEP","OCT","NOV","DIC"];
        $totals = [];

        for ($i = 1; $i <= 12; $i++) {
            $total = TotalVenta::where('ruc', $validated['cliente'])
                ->where('entregado', 'SI')
                ->where($this->connection->raw('YEAR(fecha)'), $validated['year'])
                ->where($this->connection->raw('MONTH(fecha)'), $i)
                ->select($this->connection->raw('coalesce(sum(total), 0) as total'))
                ->first();

            $totals[] = [
              "total" => $total->total,
              "month" => $months[$i -1]
            ];
        }

        return response()->json($totals);
    }

    function recordSeller(Request $request) {
        $validated = $request->validate([
            "seller" =>    "required|string",
            "year" =>       "required|numeric|digits:4|min:2015",
        ]);

        $months = ["ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SEP","OCT","NOV","DIC"];
        $totals = [];

        for ($i = 1; $i <= 12; $i++) {
            $total = TotalVenta::where('vendedor', $validated['seller'])
                ->where('entregado', 'SI')
                ->where($this->connection->raw('YEAR(fecha)'), $validated['year'])
                ->where($this->connection->raw('MONTH(fecha)'), $i)
                ->select($this->connection->raw('coalesce(sum(total), 0) as total'))
                ->first();

            $totals[] = [
                "total" => $total->total,
                "month" => $months[$i -1]
            ];
        }

        return response()->json($totals);
    }

    private function getRegister(String $type, String $serie) {
        return match ($type) {
            "FACTURA" =>                Factura::where("seriefactura", $serie)->get(),
            "FACTURA ELECTRONICA" =>    FacturaElectronica::where("seriefactura", $serie)->get(),
            "FACTURA ELECTRONICA 2" =>  FacturaElectronica2::where("seriefactura", $serie)->get(),
            "BOLETA DE VENTA" =>        Boleta::where("serieboleta", $serie)->get(),
            "BOLETA ELECTRONICA" =>     BoletaElectronica::where("serieboleta", $serie)->get(),
            "BOLETA ELECTRONICA 2" =>   BoletaElectronica2::where("serieboleta", $serie)->get(),
            "NOTA DE PEDIDO" =>         NotaPedido::where("serienota", $serie)->get(),
            "NOTA DE PEDIDO 2" =>       NotaPedido2::where("serienota", $serie)->get(),
            default => [],
        };
    }

    private function constructQrlabel($model, $total) {
        $ruc = $this->config["ruc1"].substr(5,11);
        $serie = $this->config["serie1"];
        $igv = $total * 0.18;

        if(str($model->documento)->contains("FACTURA")) $type = "|01|F";
        else if(str($model->documento)->contains("BOLETA")) $type = "|03|B";
        else return "";

        if(str($model->documento)->contains("FACTURA")) $docType = "|6|";
        else if(str($model->documento)->contains("BOLETA")) $docType = "|1|";
        else return "";

        return $ruc.$type.$serie."|".$model->serieventas."|".$igv."|".$total."|".$model->fecha.$docType.$model->ruc;
    }
}

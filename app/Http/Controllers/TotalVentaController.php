<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\TotalPedido;
use App\Models\TotalVenta;
use Illuminate\Database\Grammar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        return response()->json($query->select('cliente', 'fecha', 'fechapago', 'vendedor', 'total', 'pendiente', 'acuenta', 'serieventas', 'documento')->get());
    }

    function store(Request $request)
    {

        $validated = $request->validate([
            "comment" => "string|nullable",
            "client" => "numeric|required|exists:cliente,id_cliente",
            "products" => "required|array|min:1",
            "products.*.cant" => "numeric|required",
            "products.*.id" => "numeric|required",
            "products.*.price" => "numeric|required",
        ]);

        //usuario autenticado
        $user = auth()->user();

        //ultima serie de pedido
        $lastSeriePedido = DB::table("total_pedido")
            ->select(DB::raw("MAX(seriepedido) as max"))
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


        DB::beginTransaction();

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
            $totalPedido->documento = "PROFORMA";

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

            DB::commit();

            return response()->json([
                "result" => true,
                "message" => $next_serie
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
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
                ->where(DB::raw('YEAR(fecha)'), $validated['year'])
                ->where(DB::raw('MONTH(fecha)'), $i)
                ->select(DB::raw('coalesce(sum(total), 0) as total'))
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
                ->where(DB::raw('YEAR(fecha)'), $validated['year'])
                ->where(DB::raw('MONTH(fecha)'), $i)
                ->select(DB::raw('coalesce(sum(total), 0) as total'))
                ->first();

            $totals[] = [
                "total" => $total->total,
                "month" => $months[$i -1]
            ];
        }

        return response()->json($totals);
    }
}

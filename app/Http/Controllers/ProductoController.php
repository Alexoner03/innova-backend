<?php

namespace App\Http\Controllers;

use App\Models\Boleta;
use App\Models\BoletaElectronica;
use App\Models\BoletaElectronica2;
use App\Models\Compra;
use App\Models\Factura;
use App\Models\FacturaElectronica;
use App\Models\FacturaElectronica2;
use App\Models\NotaPedido;
use App\Models\NotaPedido2;
use App\Models\Producto;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    private readonly Connection $connection;

    public function __construct()
    {
        $connectionLabel = request()->has("db") ? request()->get("db") : auth()->payload()->get('BASE');
        $this->connection = DB::connection($connectionLabel);
    }

    function listAll()
    {

        return response()->json(
            Producto::select("id", "producto", "p_especial", "p_unidad", "p_promotor", "stock_real", "id", "cant_caja", "codigo", "marca", "p_compra")
                ->where("activo", "SI")
                ->orderBy("producto", "asc")
                ->get()
        );
    }

    function filter(Request $request)
    {
        $validated = $request->validate([
            "value" => "string"
        ]);

        $splitted = explode(" ", $validated["value"]);

        $query = Producto::select("id", "producto", "p_especial", "p_unidad", "p_promotor" , "stock_real", "id", "cant_caja", "codigo", "marca", "p_compra")
            ->where("activo", "SI");

        foreach ($splitted as $word) {
            $query->where($this->connection->raw("concat(producto,marca)"), "like", "%" . $word . "%");
        }

        return response()->json(
            $query
                ->orderBy("producto", "ASC")
                ->get());
    }

    public function history(Request $request) {

        $fields = $request->validate([
            "cliente" => 'string|nullable',
            "id" => 'numeric|required'
        ]);

        $compras = Compra::where("entregado", "SI")
            ->where("id", $fields["id"])
            ->orderBy("fecha", "desc")
            ->select("fecha", "cantidad", "producto", "unitario", "billete")
            ->limit(3)
            ->get();

        $ventas = collect();

        if($fields["cliente"] !== "" && !!$fields["cliente"]) {
            dd($fields["cliente"]);
            $pedidos = NotaPedido::where("id", $fields["id"])
                ->where($this->connection->raw("TRIM(cliente)"), str($fields["cliente"])->trim())
                ->where("entregado", "SI")
                ->select("fecha", "cantidad", "producto", "unitario")
                ->orderBy("fecha", "desc")
                ->limit(3)
                ->get();

            $pedidos2 = NotaPedido2::where("id", $fields["id"])
                ->where($this->connection->raw("TRIM(cliente)"), str($fields["cliente"])->trim())
                ->where("entregado", "SI")
                ->select("fecha", "cantidad", "producto", "unitario")
                ->orderBy("fecha", "desc")
                ->limit(3)
                ->get();

            $factElec = FacturaElectronica::where("id", $fields["id"])
                ->where($this->connection->raw("TRIM(cliente)"), str($fields["cliente"])->trim())
                ->where("entregado", "SI")
                ->select("fecha", "cantidad", "producto", "unitario")
                ->orderBy("fecha", "desc")
                ->limit(3)
                ->get();

            $factElec2 = FacturaElectronica2::where("id", $fields["id"])
                ->where($this->connection->raw("TRIM(cliente)"), str($fields["cliente"])->trim())
                ->where("entregado", "SI")
                ->select("fecha", "cantidad", "producto", "unitario")
                ->orderBy("fecha", "desc")
                ->limit(3)
                ->get();

            $boletaElec = BoletaElectronica::where("id", $fields["id"])
                ->where($this->connection->raw("TRIM(cliente)"), str($fields["cliente"])->trim())
                ->where("entregado", "SI")
                ->select("fecha", "cantidad", "producto", "unitario")
                ->orderBy("fecha", "desc")
                ->limit(3)
                ->get();

            $boletaElec2 = BoletaElectronica2::where("id", $fields["id"])
                ->where($this->connection->raw("TRIM(cliente)"), str($fields["cliente"])->trim())
                ->where("entregado", "SI")
                ->select("fecha", "cantidad", "producto", "unitario")
                ->orderBy("fecha", "desc")
                ->limit(3)
                ->get();

            $ventas = collect([
                ...$pedidos,
                ...$pedidos2,
                ...$factElec,
                ...$factElec2,
                ...$boletaElec,
                ...$boletaElec2,
            ]);
        }

        return response()->json([
            "ventas" => $ventas,
            "compras" => $compras
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Adelanto;
use Illuminate\Http\Request;

class AdelantoController extends Controller
{
    public function findBySerie(Request $request)
    {
        $validated = $request->validate([
            'serie' => 'string|min:3'
        ]);

        /*SELECT fecha,adelanto,encargado FROM adelantos WHERE serie='".$_REQUEST['serie']."'";*/
        $results = Adelanto::where('serie', $validated['serie'])->select('fecha', 'adelanto', 'encargado')->get();

        return response()->json($results);
    }
}

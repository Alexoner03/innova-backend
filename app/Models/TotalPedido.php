<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalPedido extends Model
{
    use HasFactory;

    protected $table = "total_pedido";
    protected $primaryKey = "id_ped_total";
}

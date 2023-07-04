<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaElectronica2 extends Model
{
    use HasFactory;
    protected $table  = "facturaelectronica2";
    protected $primaryKey = "idfactura";
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = request()->has("db") ? request()->get("db") : auth()->payload()->get('BASE');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletaElectronica2 extends Model
{
    use HasFactory;
    protected $table  = "boletaelectronica2";
    protected $primaryKey = "idboleta";
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = request()->has("db") ? request()->get("db") : auth()->payload()->get('BASE');
    }
}

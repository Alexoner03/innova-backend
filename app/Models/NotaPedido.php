<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaPedido extends Model
{
    use HasFactory;

    protected $table = "notapedido";
    protected $primaryKey = "idnota";

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = request()->has("db") ? request()->get("db") : auth()->payload()->get('BASE');
    }
}

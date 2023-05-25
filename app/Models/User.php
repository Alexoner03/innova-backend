<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $table = "usuario";
    protected $primaryKey = "id";

    protected $hidden = ["password"];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = request()->has("db") ? request()->get("db") : auth()->payload()->get('BASE');
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}

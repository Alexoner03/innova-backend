<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $request = request()->validate([
            "usuario" => "string",
            "password" => "string",
            "db" => "string"
        ]);

        $user = DB::connection($request["db"])
            ->table("usuario")
            ->where("usuario", $request["usuario"])
            ->where("password", $request["password"])
            ->where("activo", "SI")
            ->first();

        if($user === null) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userModel = new User();
        $userModel->id = $user->id;
        $userModel->usuario = $user->usuario;
        $userModel->password = $user->password;
        $userModel->cargo = $user->cargo;
        $userModel->nombre = $user->nombre;
        $userModel->activo = $user->activo;
        $userModel->cumple = $user->cumple;
        $userModel->celular = $user->celular;

        $token = auth()->claims(['BASE' => $request["db"]])->login($userModel);

        return response()->json([
            "auth" => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ],
            "user" => $user
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        $user->db = auth()->payload()->get("BASE");
        return response()->json($user);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}

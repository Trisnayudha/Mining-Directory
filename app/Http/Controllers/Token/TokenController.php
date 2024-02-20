<?php

namespace App\Http\Controllers\Token;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper; // Import ResponseHelper

class TokenController extends Controller
{
    use ResponseHelper; // Gunakan trait ResponseHelper

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function checkTokenData()
    {
        try {
            // Mendapatkan pengguna yang terautentikasi dari token JWT
            $user = JWTAuth::parseToken()->authenticate();

            // Jika pengguna ditemukan, kembalikan data pengguna menggunakan ResponseHelper
            return $this->sendResponse('User data retrieved successfully', ['user' => $user], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            // Token expired
            return $this->sendResponse('Token expired', null, 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            // Token invalid
            return $this->sendResponse('Token invalid', null, 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // Token tidak disediakan
            return $this->sendResponse('Token not provided', null, 401);
        }
    }
}

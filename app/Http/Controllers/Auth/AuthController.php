<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(Request $request)
    {
        try {
            // Validasi request
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // Attempt to authenticate the user
            if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
                // Return error response if authentication fails
                return $this->sendResponse('Unauthorized', null, 401);
            }

            // Get the authenticated user from the JWTAuth facade
            $user = JWTAuth::user();

            // Return success response along with the token and user data
            return $this->sendResponse('Login successful', ['token' => $token, 'user' => $user], 200);
        } catch (ValidationException $e) {
            // Return validation error response
            return $this->sendResponse('Validation Error', $e->validator->errors(), 422);
        } catch (\Exception $e) {
            // Return generic error response
            return $this->sendResponse('An error occurred', null, 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;

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
        // Validasi request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = $this->userRepository->findByEmail($request->email);

        if (!$user || !app('hash')->check($request->password, $user->password)) {
            return $this->sendResponse('Unauthorized', null, 401); // Untuk error
        }

        // Jika login berhasil, kirim response sukses
        return $this->sendResponse('Login successful', ['user' => $user], 200); // Untuk sukses
    }
}

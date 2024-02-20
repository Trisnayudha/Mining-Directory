<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(Request $request)
    {
        try {
            // Validasi data input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|string|min:6',
                'prefix_phone' => 'required|string|max:5',
                'phone' => 'required|string|max:20',
                'prefix_company' => 'required|string|max:5',
                'company_name' => 'required|string|max:255',
                'job_title' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendResponse('Validation Error', $validator->errors(), 422);
            }

            // Hash the password
            $hashedPassword = Hash::make($request->input('password'));

            // Create the user
            $user = $this->userRepository->createUsers([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $hashedPassword,
                'prefix_phone' => $request->input('prefix_phone'),
                'phone' => $request->input('phone'),
                'prefix_company' => $request->input('prefix_company'),
                'company_name' => $request->input('company_name'),
                'job_title' => $request->input('job_title'),
                'tick_marketing' => $request->input('tick_marketing'),
                'tick_explore' => $request->input('tick_explore'),
            ]);

            return $this->sendResponse('User registered successfully', $user, 201);
        } catch (\Exception $e) {
            // Return generic error response
            return $this->sendResponse('An error occurred', ['error' => $e->getMessage()], 500);
        }
    }
}

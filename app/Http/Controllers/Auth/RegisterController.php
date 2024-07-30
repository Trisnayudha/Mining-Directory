<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        DB::beginTransaction(); // Mulai transaksi
        try {
            // Validasi data input
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'phone' => 'required|unique:users',
                'prefix_phone' => 'required'
            ]);

            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'prefix_phone' => $request->prefix_phone,
                'phone' => $request->phone,
                'password' => app('hash')->make($request->password),
                'verification_token' => Str::random(60),
                'marketing' => $request->marketing,
                'explore' => $request->explore,
                'company_name' => $request->company_name,
                'job_title' => $request->job_title
            ]);
            $user->save();

            $url = url('/api/verify/' . $user->verification_token);

            // Kirim email dengan URL verifikasi
            Mail::raw("Silahkan klik pada link ini untuk verifikasi akun anda: $url", function ($message) use ($user) {
                $message->to($user->email)->subject('Verifikasi Email Anda');
            });
            DB::commit(); // Commit transaksi jika tidak ada masalah
            return $this->sendResponse('User registered successfully', null, 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->sendResponse('Validation error', $e->errors(), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendResponse('An error occurred', ['error' => $e->getMessage()], 500);
        }
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token tidak valid.'], 404);
        }

        $user->is_verified = true;
        $user->verification_token = null;
        $user->save();

        return $this->sendResponse('Akun telah terverifikasi', null, 200);
    }
}

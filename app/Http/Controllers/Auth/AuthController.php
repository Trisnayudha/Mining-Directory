<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class AuthController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function loginPassword(Request $request)
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

            // Create a custom token with user's role
            $token = JWTAuth::claims(['role' => 'users'])->attempt($request->only('email', 'password'));

            // Return success response along with the token
            return $this->sendResponse('Login successful', ['token' => $token], 200);
        } catch (ValidationException $e) {
            // Return validation error response
            return $this->sendResponse('Validation Error', $e->validator->errors(), 422);
        } catch (\Exception $e) {
            // Return generic error response
            return $this->sendResponse('An error occurred', null, 500);
        }
    }


    public function requestOtp(Request $request)
    {
        $type = $request->type;
        $email = $request->email;
        try {
            // Generate a random six-digit OTP
            $otp = rand(100000, 999999);
            // Setting OTP to cache with a 10-minute expiration
            Cache::put($email, $otp, 600); // 600 seconds or 10 minutes
            $user = User::where('email', $email)->first();
            if (empty($user)) {
                return $this->sendResponse('User not found', null, 404);
            }
            if ($type == 'email') {
                //
                $sendEmail = new EmailSender();
                $sendEmail->from = 'Mining Directory';
                $sendEmail->subject = "OTP Login";
                $wording = 'We received a request to login your account. To login, please use this
                    code:';
                $sendEmail->template = "email.tokenverify";
                $sendEmail->data = [
                    'wording' => $wording,
                    'otp' => $otp
                ];
                $sendEmail->from = env('MAIL_FROM_ADDRESS');
                $sendEmail->name_sender = env('MAIL_FROM_NAME');
                $sendEmail->to = $email;
                $sendEmail->sendEmail();
            } else {
                //
                $wa = new WhatsappApi();
                $wa->phone = $user->phone;
                $wa->message = 'OTP: '
                    . $otp;
                $wa->WhatsappMessage();
            }
            return $this->sendResponse('Send OTP successful', null, 200);
        } catch (\Exception $e) {
            // Return generic error response
            return $this->sendResponse('An error occurred', null, 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $identifier = $request->email; // This could be an email or a phone number depending on your system
        $userInputOtp = $request->otp; // OTP provided by the user

        try {
            // Retrieve the OTP from cache
            $cachedOtp = Cache::get($identifier);

            // Check if there's an OTP and if it matches the user's input
            if ($cachedOtp && $cachedOtp == $userInputOtp) {
                // If the OTP is correct, you can proceed with login or other action
                Cache::forget($identifier); // Optionally, clear the OTP from cache after successful verification
                // Attempt to authenticate the user
                // Find the user by email
                $user = User::where('email', $identifier)->first();
                if (!$user) {
                    return $this->sendResponse('User not found', null, 404);
                }

                // Manually create a token for the user
                $token = JWTAuth::fromUser($user);

                // Return success response along with the token and user data
                return $this->sendResponse('Login successful', ['token' => $token, 'user' => $user], 200);
            } else {
                // If the OTP is incorrect or expired
                return $this->sendResponse('OTP is incorrect or has expired', null, 401); // 401 Unauthorized
            }
        } catch (\Exception $e) {
            // Return a generic error response if an exception occurs
            return $this->sendResponse('An error occurred: ' . $e->getMessage(), null, 500);
        }
    }
}

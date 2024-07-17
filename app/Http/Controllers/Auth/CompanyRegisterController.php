<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Models\Company;
use App\Models\CompanyCategory;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompanyRegisterController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerPersonalInfo(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validasi data input
            $this->validate($request, [
                'name_representative' => 'required',
                'job_title_representative' => 'required',
                'phone_representative' => 'required',
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // Cek apakah email sudah terdaftar
            $existingUser = Company::where('email', $request->email)->first();
            if ($existingUser) {
                if ($existingUser->verify_email) {
                    return $this->sendResponse('Email is already verified and cannot be used again', null, 422);
                } else {
                    // Update data personal information
                    $existingUser->name_representative = $request->name_representative;
                    $existingUser->job_title_representative = $request->job_title_representative;
                    $existingUser->prefix_phone_representative = $request->prefix_phone_representative;
                    $existingUser->phone_representative = $request->phone_representative;
                    $existingUser->password = app('hash')->make($request->password);
                    $existingUser->save();

                    DB::commit();

                    return $this->sendResponse('Personal information updated successfully', ['user_id' => $existingUser->id], 200);
                }
            }

            // Buat pengguna baru
            $user = new Company([
                'name_representative' => $request->name_representative,
                'job_title_representative' => $request->job_title_representative,
                'prefix_phone_representative' => $request->prefix_phone_representative,
                'phone_representative' => $request->phone_representative,
                'email' => $request->email,
                'password' => app('hash')->make($request->password),
                'verify_email' => false // Atur verify_email ke false pada awalnya
            ]);

            $user->save();

            DB::commit();

            return $this->sendResponse('Personal information saved successfully', ['user_id' => $user->id], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->sendResponse('Validation error', $e->errors(), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendResponse('An error occurred', ['error' => $e->getMessage()], 500);
        }
    }

    public function registerCompanyInfo(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validasi data input
            $this->validate($request, [
                'user_id' => 'required|exists:company,id',
                'company_website' => 'required',
                'country' => 'required',
                'state' => 'required',
                'city' => 'required',
                'postal_code' => 'required',
                'prefix_phone_company' => 'required',
                'phone_company' => 'required',
            ]);

            $user = Company::find($request->user_id);

            // Cek apakah verify_email bernilai true
            if ($user->verify_email) {
                return $this->sendResponse('Email is already verified and cannot be used to update company information', null, 422);
            }

            // Update data company information
            $user->website = $request->company_website;
            $user->country = $request->country;
            $user->state = $request->state;
            $user->city = $request->city;
            $user->postal_code = $request->postal_code;
            $user->prefix_phone_company = $request->prefix_phone_company;
            $user->phone_company = $request->phone_company;
            $user->verification_token = Str::random(60);
            $user->marketing = $request->marketing;
            $user->explore = $request->explore;

            $user->save();

            if (!empty($request->company_category)) {
                foreach ($request->company_category as $data) {
                    $company_category = new CompanyCategory();
                    $company_category->company_id = $user->id;
                    $company_category->category_id = $data;
                    $company_category->save();
                }
            }

            if (!empty($request->agree)) {
                $url = url('/api/company/verify/' . $user->verification_token);
                // Kirim email dengan URL verifikasi
                Mail::raw("Silahkan klik pada link ini untuk verifikasi akun anda: $url", function ($message) use ($user) {
                    $message->to($user->email)->subject('Company: Verifikasi Email Anda');
                });
            }

            DB::commit();
            return $this->sendResponse('Company information saved successfully', null, 201);
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
        $user = Company::where('verification_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token tidak valid.'], 404);
        }

        $user->verify_email = true;
        $user->verification_token = null;
        $user->save();

        return $this->sendResponse('Akun telah terverifikasi', null, 200);
    }
}

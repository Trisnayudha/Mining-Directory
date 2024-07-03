<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    use ResponseHelper;

    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    protected function getAuthenticatedUserId()
    {
        return JWTAuth::parseToken()->authenticate()->id;
    }

    public function detail()
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->user->getDetail($userId);

        if (!$data) {
            return $this->sendResponse('User not found', [], 404);
        }

        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function editProfile(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->user->editProfile($request, $userId);
        if (!$data) {
            return $this->sendResponse('Failed to update profile', [], 400);
        }

        return $this->sendResponse('Successfully updated data', $data, 200);
    }

    public function checkEmail(Request $request)
    {
        //
        $data = $this->user->findByEmail($request->email);
        if (!$data) {
            return $this->sendResponse('Email is available', [], 200);
        }
        return $this->sendResponse('Email is already in use. Please try another one.', [], 409);
    }

    public function editProfileDetail(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->user->editProfileDetail($request, $userId);
        return $this->sendResponse('Successfully updated data', $data, 200);
    }
}

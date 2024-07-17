<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\DashboardRepository;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini

    protected $dashboard;

    public function __construct(DashboardRepository $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    protected function getAuthenticatedUserId()
    {
        // Autentikasi menggunakan guard 'company'
        return  Auth::guard('company')->user()->id;
    }

    public function card(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->dashboard->card($userId, $request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function listVisitor(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->dashboard->listVisitor($userId, $request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function listInquiry(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->dashboard->listInquiry($userId, $request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function approveInquiry(Request $request)
    {
        $data = $this->dashboard->approveInquiry($request);
        return $this->sendResponse('Successfully post data', $data, 201);
    }

    public function listBusinessCard(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->dashboard->listBusinessCard($userId, $request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\HelpRepository;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class HelpController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $help;
    protected function getAuthenticatedUserId()
    {
        $userId = null;
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
        } catch (\Exception $e) {
            // Token tidak ada atau tidak valid, biarkan $userId tetap null
        }
        return $userId;
    }

    public function __construct(HelpRepository $help)
    {
        $this->help = $help;
    }

    public function faqHome(Request $request)
    {
        $data = $this->help->faqHome($request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function faqProfile(Request $request)
    {
        $data = $this->help->faqProfile($request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function contactUs(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->help->contactUs($request, $userId);
        return $this->sendResponse('Successfully sent data', $data, 200);
    }

    public function privacyPolicy()
    {
        $data = $this->help->privacy();
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function termCondition()
    {
        $data = $this->help->term();
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\CompanyRepository;
use App\Traits\CompanyLogTrait;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CompanyController extends Controller
{
    use ResponseHelper, CompanyLogTrait; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $company;
    public function __construct(CompanyRepository $company)
    {
        $this->company = $company;
    }

    protected function getAuthenticatedUserId()
    {
        return JWTAuth::parseToken()->authenticate()->id;
    }

    public function list(Request $request)
    {
        $data = $this->company->findSearch($request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function detail($slug)
    {
        $userId = null;
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
        } catch (\Exception $e) {
            // Token tidak ada atau tidak valid, biarkan $userId tetap null
        }

        $data = $this->company->findDetail($slug, $userId);

        // Log company detail view
        if ($data && $userId) {
            $this->logCompanyDetail($data->id, $userId);
        }

        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function sectionDetail($slug, Request $request)
    {
        $data = $this->company->findDetailSection($slug, $request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function addFavorite(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->company->addFavorite($request, $userId);
        return $this->sendResponse('Successfully action', $data, 200);
    }

    public function addBusinessCard(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->company->addBusinessCard($request, $userId);
        return $this->sendResponse('Successfully action', $data, 200);
    }

    public function addInquiry(Request $request)
    {
        $userId = null;
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
        } catch (\Exception $e) {
            // Token tidak ada atau tidak valid, biarkan $userId tetap null
        }

        $data = $this->company->addInquiry($request, $userId);
        return $this->sendResponse('Successfully sent inquiry', $data, 200);
    }
}

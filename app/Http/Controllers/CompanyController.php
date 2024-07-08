<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\CompanyRepository;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CompanyController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
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

    public function detail($slug)
    {
        $data = $this->company->findDetail($slug);
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
        //
    }
}

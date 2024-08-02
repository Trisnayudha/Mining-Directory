<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\VideosRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyVideosController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $videos;
    public function __construct(VideosRepository $videos)
    {
        $this->videos = $videos;
    }

    protected function getAuthenticatedCompanyId()
    {
        // Autentikasi menggunakan guard 'company'
        return  Auth::guard('company')->user()->id;
    }

    public function index()
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->videos->cIndex($companyId);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function store(Request $request)
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->videos->cStore($companyId, $request);
        return $this->sendResponse('Successfully store data', $data, 200);
    }

    public function edit($slug)
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->videos->cEdit($companyId, $slug);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function destroy($slug)
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->videos->cDestroy($companyId, $slug);

        return $this->sendResponse('Successfully Delete data', $data, 200);
    }

    public function update($id, Request $request)
    {
        $data = $this->videos->cUpdate($id, $request);
        return $this->sendResponse('Successfully updated data', $data, 200);
    }

    public function listing(Request $request)
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->videos->cListing($companyId, $request);
        return $this->sendResponse('Successfully listing data', $data, 200);
    }
}

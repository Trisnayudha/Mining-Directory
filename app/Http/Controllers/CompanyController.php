<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\CompanyRepository;
use Illuminate\Http\Request;

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
}

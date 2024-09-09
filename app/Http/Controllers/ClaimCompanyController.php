<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\ClaimCompanyRepository;
use Illuminate\Http\Request;

class ClaimCompanyController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $company;

    public function __construct(ClaimCompanyRepository $company)
    {
        $this->company = $company;
    }


    public function store(Request $request)
    {
        $data = $this->company->claim($request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\CompanyInformationRepository;
use Illuminate\Support\Facades\Auth;

class CompanyInformationController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $company;

    public function __construct(CompanyInformationRepository $company)
    {
        $this->company = $company;
    }

    protected function getAuthenticatedUserId()
    {
        // Autentikasi menggunakan guard 'company'
        return  Auth::guard('company')->user()->id;
    }

    public function index()
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->company->detail($userId);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function store()
    {
    }
}

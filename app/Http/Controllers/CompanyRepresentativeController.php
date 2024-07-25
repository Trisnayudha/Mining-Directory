<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\CompanyRepresentativeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyRepresentativeController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini

    protected $company;

    public function __construct(CompanyRepresentativeRepository $company)
    {
        $this->company = $company;
    }

    protected function getAuthenticatedUserId()
    {
        // Autentikasi menggunakan guard 'company'
        return Auth::guard('company')->user()->id;
    }

    public function index()
    {
        $userId = $this->getAuthenticatedUserId();
        $data = $this->company->index($userId);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function store(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();
        $payload = $request->only(['name', 'image', 'email', 'code_phone', 'phone', 'country', 'job_title']);
        $companyRepresentative = $this->company->store($userId, $payload);

        return $this->sendResponse('Successfully created representative', $companyRepresentative, 201);
    }

    public function update(Request $request, $id)
    {
        $payload = $request->only(['name', 'image', 'email', 'code_phone', 'phone', 'country', 'job_title']);
        $companyRepresentative = $this->company->update($id, $payload);

        if ($companyRepresentative) {
            return $this->sendResponse('Successfully updated representative', $companyRepresentative, 200);
        }

        return $this->sendResponse('Representative not found', null, 404);
    }

    public function delete($id)
    {
        $deleted = $this->company->delete($id);

        if ($deleted) {
            return $this->sendResponse('Successfully deleted representative', null, 200);
        }

        return $this->sendResponse('Representative not found', null, 404);
    }
}

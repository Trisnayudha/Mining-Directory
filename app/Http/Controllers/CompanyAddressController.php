<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\CompanyAddressRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyAddressController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini

    protected $company;

    public function __construct(CompanyAddressRepository $company)
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
        $payload = $request->only(['type', 'town', 'country', 'phone', 'address', 'city', 'province', 'postal_code']);
        $companyAddress = $this->company->store($userId, $payload);

        return $this->sendResponse('Successfully created address', $companyAddress, 201);
    }

    public function update(Request $request, $id)
    {
        $payload = $request->only(['type', 'town', 'country', 'phone', 'address', 'city', 'province', 'postal_code']);
        $companyAddress = $this->company->update($id, $payload);

        if ($companyAddress) {
            return $this->sendResponse('Successfully updated address', $companyAddress, 200);
        }

        return $this->sendResponse('Address not found', null, 404);
    }

    public function delete($id)
    {
        $deleted = $this->company->delete($id);

        if ($deleted) {
            return $this->sendResponse('Successfully deleted address', null, 200);
        }

        return $this->sendResponse('Address not found', null, 404);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyProductController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $product;
    public function __construct(ProductRepository $product)
    {
        $this->product = $product;
    }

    protected function getAuthenticatedCompanyId()
    {
        // Autentikasi menggunakan guard 'company'
        return  Auth::guard('company')->user()->id;
    }

    public function index()
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->product->cIndex($companyId);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function store(Request $request)
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->product->cStore($companyId, $request);
        return $this->sendResponse('Successfully store data', $data, 200);
    }

    public function edit($slug)
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->product->cEdit($companyId, $slug);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function destroy($slug)
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->product->cDestroy($companyId, $slug);

        return $this->sendResponse('Successfully Delete data', $data, 200);
    }

    public function update($id, Request $request)
    {
        $data = $this->product->cUpdate($id, $request);
        return $this->sendResponse('Successfully updated data', $data, 200);
    }

    public function listing(Request $request)
    {
        $companyId = $this->getAuthenticatedCompanyId();
        $data = $this->product->cListing($companyId, $request);
        return $this->sendResponse('Successfully listing data', $data, 200);
    }
}

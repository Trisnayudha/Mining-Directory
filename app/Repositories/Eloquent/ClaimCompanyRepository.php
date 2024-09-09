<?php

namespace App\Repositories\Eloquent;

use App\Models\ClaimCompany;
use App\Repositories\Contracts\ClaimCompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClaimCompanyRepository implements ClaimCompanyRepositoryInterface
{
    protected $model;

    public function __construct(ClaimCompany $model)
    {
        $this->model = $model;
    }

    /**
     * Store claim company data.
     *
     * @param \Illuminate\Http\Request $request
     * @return ClaimCompany
     */
    public function claim($request)
    {
        // Using DB transaction in case any error occurs during the insert operation
        DB::beginTransaction();
        try {
            // Create a new ClaimCompany instance and save it
            $claimCompany = $this->model->create([
                'company_id' => $request->input('company_id'),
                'full_name' => $request->input('full_name'),
                'position_title' => $request->input('position_title'),
                'email' => $request->input('email'),
                'alternate_email' => $request->input('alternate_email'),
                'code_phone' => $request->input('code_phone'),
                'phone' => $request->input('phone'),
                'company_name' => $request->input('company_name'),
                'company_category' => $request->input('company_category'),
                'classification_company' => $request->input('classification_company'),
                'project_type' => $request->input('project_type'),
                'company_address' => $request->input('company_address'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'country' => $request->input('country'),
                'postal_code' => $request->input('postal_code'),
                'code_company_phone' => $request->input('code_company_phone'),
                'company_phone_number' => $request->input('company_phone_number'),
                'company_email' => $request->input('company_email'),
                'company_website' => $request->input('company_website'),
            ]);

            // Commit the transaction
            DB::commit();

            // Return the created ClaimCompany model instance
            return $claimCompany;
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Error storing claim company data: ' . $e->getMessage());

            // Optionally throw an exception if you want to handle this at a higher level
            throw new \Exception('Error storing claim company data.');
        }
    }
}

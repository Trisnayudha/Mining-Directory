<?php

namespace App\Repositories\Eloquent;

use App\Models\ClaimCompany;
use App\Repositories\Contracts\ClaimCompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\EmailSender;

class ClaimCompanyRepository implements ClaimCompanyRepositoryInterface
{
    protected $model;

    public function __construct(ClaimCompany $model)
    {
        $this->model = $model;
    }

    /**
     * Store claim company data and send email notifications.
     *
     * @param \Illuminate\Http\Request $request
     * @return ClaimCompany
     */
    public function claim($request)
    {
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

            DB::commit();

            // Send email notification to the user
            $this->sendClaimNotification($claimCompany);

            // Send notification email to the admin
            $this->sendAdminNotification($claimCompany);

            return $claimCompany;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing claim company data: ' . $e->getMessage());
            throw new \Exception('Error storing claim company data.');
        }
    }

    /**
     * Send email notification to the user who submitted the claim.
     *
     * @param ClaimCompany $claimCompany
     * @return void
     */
    private function sendClaimNotification($claimCompany)
    {
        $emailSender = new EmailSender();
        $emailSender->template = 'email.claim_notification'; // Assuming you have a view for the email template
        $emailSender->data = [
            'full_name' => $claimCompany->full_name,
            'company_name' => $claimCompany->company_name
        ];
        $emailSender->from = 'no-reply@indonesiaminer.com';
        $emailSender->name_sender = 'Indonesia Miner';
        $emailSender->to = $claimCompany->email;
        $emailSender->subject = 'Your Company Claim is Being Processed';

        $emailSender->sendEmail();
    }

    /**
     * Send email notification to the admin with claim details.
     *
     * @param ClaimCompany $claimCompany
     * @return void
     */
    private function sendAdminNotification($claimCompany)
    {
        $emailSender = new EmailSender();
        $emailSender->template = 'email.claim_admin_notification'; // Assuming you have a view for the email template
        $emailSender->data = [
            'full_name' => $claimCompany->full_name,
            'position_title' => $claimCompany->position_title,
            'company_name' => $claimCompany->company_name,
            'email' => $claimCompany->email,
            'company_phone_number' => $claimCompany->company_phone_number,
            'company_category' => $claimCompany->company_category
        ];
        $emailSender->from = 'no-reply@indonesiaminer.com';
        $emailSender->name_sender = 'Indonesia Miner';
        $emailSender->to = 'yudha@indonesiaminer.com';
        $emailSender->subject = 'New Company Claim Submission';

        $emailSender->sendEmail();
    }
}

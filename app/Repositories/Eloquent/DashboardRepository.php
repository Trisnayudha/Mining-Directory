<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Models\CompanyBusinessCard;
use App\Models\CompanyInquiry;
use App\Models\CompanyLog;
use App\Models\Example;
use App\Models\MediaResource;
use App\Models\News;
use App\Models\Product;
use App\Models\Project;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    protected $company;
    protected $companyInquiry;
    protected $companyLog;

    public function __construct(
        Company $company,
        CompanyInquiry $companyInquiry,
        CompanyLog $companyLog
    ) {
        $this->company = $company;
        $this->companyInquiry = $companyInquiry;
        $this->companyLog = $companyLog;
    }

    public function card($id, $request)
    {
        $countInquiry = $this->countInquiry($id, $request);
        $countVisitor = $this->countVisitor($id, $request);
        $countAsset = $this->countAsset($id);

        $array = [
            'asset' => $countAsset,
            'visitor' => $countVisitor,
            'inquiry' => $countInquiry
        ];

        return $array;
    }

    public function listVisitor($id, $request)
    {
        $limit = $request->limit;
        return CompanyLog::join('users', 'users.id', 'company_log.users_id')->select('name', 'users.company_name', 'job_title', 'email')->where('company_id', $id)->orderby('company_log.id', 'desc')->paginate($limit);
    }

    public function listInquiry($id, $request)
    {
        $limit = $request->limit ?? 5;
        $inquiries = CompanyInquiry::leftJoin('users', 'users.id', 'company_inquiry.users_id')
            ->select('company_inquiry.*', 'users.name as users_name', 'users.email as users_email')
            ->orderby('company_inquiry.id', 'desc')
            ->paginate($limit);

        $inquiries->getCollection()->transform(function ($inquiry) {
            if ($inquiry->users_name !== null) {
                $inquiry->name = $inquiry->users_name;
            }
            if ($inquiry->users_email !== null) {
                $inquiry->email = $inquiry->users_email;
            }
            return $inquiry;
        });

        return $inquiries;
    }

    public function approveInquiry($request)
    {
        $action = $request->action;
        $inquiryId = $request->inquiry_id;
        $inquiry = CompanyInquiry::find($inquiryId);
        $inquiry->status = $action;
        $inquiry->save();
        return $inquiry;
    }


    public function listBusinessCard($id, $request)
    {
        $limit = $request->limit ?? 5;
        return CompanyBusinessCard::join('users', 'users.id', 'company_users_buisnesscard.users_id')
            ->select(
                'users.name as sender',
                'users.email',
                'users.company_name',
                'users.job_title',
                'users.prefix_phone',
                'users.phone'
            )
            ->where('company_id', $id)->paginate($limit);
    }

    private function countInquiry($id, $request)
    {
        return CompanyInquiry::where('company_id', $id)->count();
    }

    private function countVisitor($id, $request)
    {
        return CompanyLog::where('company_id', $id)->count();
    }

    private function countAsset($id)
    {
        $product = Product::where('company_id', $id)->count();
        $project = Project::where('company_id', $id)->count();
        $media = MediaResource::where('company_id', $id)->count();
        $news = News::where('company_id', $id)->count();

        $total = $product + $project + $media + $news;
        return $total;
    }
}

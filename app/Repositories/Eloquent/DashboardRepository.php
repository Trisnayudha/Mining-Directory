<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
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

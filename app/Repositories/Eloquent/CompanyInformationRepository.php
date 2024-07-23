<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Models\Example;
use App\Models\Product;
use App\Repositories\Contracts\CompanyInformationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompanyInformationRepository implements CompanyInformationRepositoryInterface
{
    protected $model;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }

    public function detail($id)
    {
        return $this->model->where('id', $id)->first();
    }
}

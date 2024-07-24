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

    public function store($id, $request)
    {
        $user = $this->model->where('id', $id)->first();

        // Mengecualikan password dari data yang akan di-update
        $data = $request->except('password');

        // Update data user tanpa password
        $user->update($data);

        return $user;
    }
}

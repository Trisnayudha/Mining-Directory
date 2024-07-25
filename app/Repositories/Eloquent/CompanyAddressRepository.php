<?php

namespace App\Repositories\Eloquent;

use App\Models\CompanyAddress;
use App\Models\Example;
use App\Models\Product;
use App\Repositories\Contracts\CompanyAddressRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompanyAddressRepository implements CompanyAddressRepositoryInterface
{
    protected $model;

    public function __construct(CompanyAddress $model)
    {
        $this->model = $model;
    }

    public function index($id)
    {
        return $this->model->where('company_id', $id)->get();
    }

    public function store($id, $payload)
    {
        // Menambahkan company_id ke payload
        $payload['company_id'] = $id;

        // Membuat alamat perusahaan baru
        $companyAddress = $this->model->create($payload);

        return $companyAddress;
    }

    public function update($id, $payload)
    {
        // Menemukan alamat perusahaan berdasarkan id
        $companyAddress = $this->model->find($id);

        if ($companyAddress) {
            // Memperbarui alamat perusahaan
            $companyAddress->update($payload);

            return $companyAddress;
        }

        return null;
    }

    public function delete($id)
    {
        $companyAddress = $this->model->find($id);

        if ($companyAddress) {
            $companyAddress->delete();
            return true;
        }

        return false;
    }
}

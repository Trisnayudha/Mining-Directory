<?php

namespace App\Repositories\Eloquent;

use App\Models\MdCategoryCompany;
use App\Models\Product;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected $model;

    public function __construct(MdCategoryCompany $model)
    {
        $this->model = $model;
    }

    public function findAll()
    {
        // Mengambil semua kategori beserta subkategori terkait
        return $this->model->with('subCategories')->get();
    }

    public function popular()
    {
        // Mengambil kategori yang tercatat sebagai populer
        return $this->model->whereHas('popular')->with('subCategories')->get();
    }
}

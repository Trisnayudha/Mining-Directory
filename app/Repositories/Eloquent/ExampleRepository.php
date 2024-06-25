<?php

namespace App\Repositories\Eloquent;

use App\Models\Example;
use App\Models\Product;
use App\Repositories\Contracts\ExampleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExampleRepository implements ExampleRepositoryInterface
{
    protected $model;

    public function __construct(Example $model)
    {
        $this->model = $model;
    }

    public function find($request)
    {
        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided

        $query = $this->model->newQuery();

        return $query;
    }
}

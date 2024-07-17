<?php

namespace App\Repositories\Eloquent;

use App\Models\Example;
use App\Models\Product;
use App\Repositories\Contracts\HomeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HomeRepository implements HomeRepositoryInterface
{
    protected $model;

    public function __construct(Example $model)
    {
        $this->model = $model;
    }

    public function carousel()
    {
        return DB::table('md_carousel')->orderby('id', 'desc')->get();
    }

    public function statistic()
    {
        return DB::table('md_statistic')->get();
    }
}

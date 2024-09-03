<?php

namespace App\Repositories\Eloquent;

use App\Models\Example;
use App\Models\Price;
use App\Models\Product;
use App\Repositories\Contracts\PriceRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PriceRepository implements PriceRepositoryInterface
{
    protected $model;

    public function __construct(Price $model)
    {
        $this->model = $model;
    }

    public function getPrice()
    {
        return $this->model->get();
    }
}

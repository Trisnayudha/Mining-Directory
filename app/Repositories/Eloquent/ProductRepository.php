<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function findSearch($request)
    {
        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided

        $query = $this->model->newQuery();
        // Join with category and subcategory tables
        $query->join('company', 'company.id', '=', 'products.company_id')
            ->leftJoin('products_asset', function ($join) {
                $join->on('products_asset.product_id', '=', 'products.id')
                    ->where('products_asset.asset_type', '=', 'png');
            })
            ->leftJoin('products_category_list', 'products_category_list.product_id', '=', 'products.id')
            ->leftJoin('md_category_company', 'products_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('products_sub_category_list', 'products_sub_category_list.product_id', '=', 'products.id')
            ->leftJoin('md_sub_category_company', 'products_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        // Search for title and description
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('products.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('products.description', 'LIKE', '%' . $search . '%');
            });
        }

        // Filter by category name if provided
        if (!empty($category_name)) {
            $query->where('md_category_company.name', 'LIKE', '%' . $category_name . '%');
        }

        // Filter by sub-category name if provided
        if (!empty($sub_category_name)) {
            $query->where('md_sub_category_company.name', 'LIKE', '%' . $sub_category_name . '%');
        }

        // Select the required columns and group by product.id
        $results = $query->select([
            'products.title',
            'products.slug',
            'products_asset.asset as image', // Get the asset from products_asset with asset_type png
            'company.company_name',
            'products.views',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('products.id', 'products.title', 'products.slug', 'products_asset.asset', 'company.company_name', 'products.views', 'company.package')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }
}

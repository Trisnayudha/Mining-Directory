<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class CompanyRepository implements CompanyRepositoryInterface
{
    protected $model;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }

    public function findList($limit)
    {
        //
    }

    public function findDetail($slug)
    {
        //
    }

    public function findSearch($request)
    {
        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided
        $location = $request->location;

        $query = $this->model->newQuery();
        // Join with category and subcategory tables
        $query->leftJoin('company_category_list', 'company_category_list.company_id', '=', 'company.id')
            ->leftJoin('md_category_company', 'company_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('company_sub_category_list', 'company_sub_category_list.company_id', '=', 'company.id')
            ->leftJoin('md_sub_category_company', 'company_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        // Search for company_name, description, value_1, value_2, value_3
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('company.company_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('company.description', 'LIKE', '%' . $search . '%')
                    ->orWhere('company.value_1', 'LIKE', '%' . $search . '%')
                    ->orWhere('company.value_2', 'LIKE', '%' . $search . '%')
                    ->orWhere('company.value_3', 'LIKE', '%' . $search . '%');
            });
        }

        // Filter by location
        if (!empty($location)) {
            $query->where('company.location', 'LIKE', '%' . $location . '%');
        }

        // Filter by category name if provided
        if (!empty($category_name)) {
            $query->where('md_category_company.name', 'LIKE', '%' . $category_name . '%');
        }

        // Filter by sub-category name if provided
        if (!empty($sub_category_name)) {
            $query->where('md_sub_category_company.name', 'LIKE', '%' . $sub_category_name . '%');
        }

        // Order results to prioritize search fields
        $query->orderByRaw("
            CASE
                WHEN company.company_name LIKE ? THEN 1
                WHEN company.description LIKE ? THEN 2
                WHEN company.value_1 LIKE ? THEN 3
                WHEN company.value_2 LIKE ? THEN 3
                WHEN company.value_3 LIKE ? THEN 3
                WHEN company.location LIKE ? THEN 4
                ELSE 5
            END", [
            '%' . $search . '%',
            '%' . $search . '%',
            '%' . $search . '%',
            '%' . $search . '%',
            '%' . $search . '%',
            '%' . $location . '%'
        ]);

        // Get all results
        $results = $query->get(['company.*']);
        // Classify results into packages
        $platinum = $results->where('package', 'platinum');
        $gold = $results->where('package', 'gold');
        $silver = $results->where('package', 'silver');

        // Paginate each package
        $platinum_paginated = $this->paginateCollection($platinum, $paginate);
        $gold_paginated = $this->paginateCollection($gold, $paginate);
        $silver_paginated = $this->paginateCollection($silver, $paginate);

        // Prepare payload
        $payload = [
            'platinum' => $platinum_paginated->items(),
            'gold' => $gold_paginated->items(),
            'silver' => $silver_paginated->items(),
            'current_page' => $platinum_paginated->currentPage(), // Assuming all packages use the same pagination settings
            'total' => $results->count(), // Total count of all results
            'per_page' => $paginate,
            'last_page' => $platinum_paginated->lastPage(), // Assuming all packages use the same pagination settings
            'from' => $platinum_paginated->firstItem(), // Assuming all packages use the same pagination settings
            'to' => $platinum_paginated->lastItem() // Assuming all packages use the same pagination settings
        ];

        return $payload;
    }

    protected function paginateCollection($items, $perPage)
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }
}

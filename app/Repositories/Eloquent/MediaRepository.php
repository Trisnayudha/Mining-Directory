<?php

namespace App\Repositories\Eloquent;

use App\Models\MediaResource;
use App\Repositories\Contracts\MediaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MediaRepository implements MediaRepositoryInterface
{
    protected $model;

    public function __construct(MediaResource $model)
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
        $query->join('company', 'company.id', '=', 'media_resource.company_id')
            ->leftJoin('media_resource_category_list', 'media_resource_category_list.media_resource_id', '=', 'media_resource.id')
            ->leftJoin('md_category_company', 'media_resource_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('media_resource_sub_category_list', 'media_resource_sub_category_list.media_resource_id', '=', 'media_resource.id')
            ->leftJoin('md_sub_category_company', 'media_resource_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('media_resource.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('media_resource.description', 'LIKE', '%' . $search . '%');
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
            'media_resource.title',
            'media_resource.slug',
            'media_resource.image', // Get the asset from media_resource_asset with asset_type png
            'company.company_name',
            'media_resource.views',
            'media_resource.download',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('media_resource.id', 'media_resource.title', 'media_resource.slug', 'media_resource.image', 'company.company_name', 'media_resource.views', 'company.package')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }
}

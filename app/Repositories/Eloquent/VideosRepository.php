<?php

namespace App\Repositories\Eloquent;

use App\Models\Videos;
use App\Repositories\Contracts\VideosRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VideosRepository implements VideosRepositoryInterface
{
    protected $model;

    public function __construct(Videos $model)
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
        $query->join('company', 'company.id', '=', 'videos.company_id')
            ->leftJoin('videos_category_list', 'videos_category_list.videos_id', '=', 'videos.id')
            ->leftJoin('md_category_company', 'videos_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('videos_sub_category_list', 'videos_sub_category_list.videos_id', '=', 'videos.id')
            ->leftJoin('md_sub_category_company', 'videos_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('videos.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('videos.description', 'LIKE', '%' . $search . '%');
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
            'videos.title',
            'videos.slug',
            'videos.asset', // Get the asset from project_asset with asset_type png
            'company.company_name',
            'videos.views',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('videos.id', 'videos.title', 'videos.slug', 'videos.asset', 'company.company_name', 'videos.views', 'company.package')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }

    public function detail($slug)
    {
        $video = $this->model->newQuery()
            ->join('company', 'company.id', 'videos.company_id')
            ->where('videos.slug', $slug)
            ->select(
                'videos.id',
                'company.company_name',
                'company.slug as company_slug',
                'company.package',
                'company.image as company_image',
                'videos.title',
                'videos.slug',
                'videos.asset',
                'videos.views',
                'videos.description',
            )
            ->with(['videoCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        // Mengambil nama kategori
        if ($video && $video->videoCategories->isNotEmpty()) {
            $video->category_name = $video->videoCategories->first()->mdCategory->name;
            unset($video->videoCategories); // Opsional: Hapus data videoCategories yang tidak perlu
        }

        return $video;
    }
}

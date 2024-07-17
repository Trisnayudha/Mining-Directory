<?php

namespace App\Repositories\Eloquent;

use App\Models\News;
use App\Repositories\Contracts\NewsRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NewsRepository implements NewsRepositoryInterface
{
    protected $model;

    public function __construct(News $model)
    {
        $this->model = $model;
    }

    public function findHome()
    {
        return $this->model->join('company', 'company.id', 'news.company_id')->select('news.*', 'company.company_name')->take(5)->get();
    }
    public function findSearch($request)
    {
        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided
        $slug = $request->slug;

        $query = $this->model->newQuery();
        // Join with category and subcategory tables
        $query->join('company', 'company.id', '=', 'news.company_id')
            ->leftJoin('news_category_list', 'news_category_list.news_id', '=', 'news.id')
            ->leftJoin('md_category_company', 'news_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('news_sub_category_list', 'news_sub_category_list.news_id', '=', 'news.id')
            ->leftJoin('md_sub_category_company', 'news_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        if (!empty($slug)) {
            $query->where('company.slug', $slug);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('news.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('news.description', 'LIKE', '%' . $search . '%');
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
            'news.title',
            'news.slug',
            'news.image', // Get the asset from project_asset with asset_type png
            'company.company_name',
            'news.views',
            'news.date_news',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('news.id', 'news.title', 'news.slug', 'news.image', 'company.company_name', 'news.views', 'company.package', 'news.date_news')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }

    public function detail($slug)
    {
        $news = $this->model->newQuery()
            ->join('company', 'company.id', '=', 'news.company_id')
            ->where('news.slug', $slug)
            ->select(
                'news.id',
                'company.company_name',
                'company.package',
                'company.slug as company_slug',
                'company.image as company_image',
                'news.views',
                'news.date_news',
                'news.title',
                'news.sub_title',
                'news.slug',
                'news.image',
                'news.description'
            )->with(['newsCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        // Mengambil nama kategori
        if ($news && $news->newsCategories->isNotEmpty()) {
            $news->category_name = $news->newsCategories->first()->mdCategory->name;
            unset($news->newsCategories); // Opsional: Hapus data projectCategories yang tidak perlu
        }
        return $news;
    }
}

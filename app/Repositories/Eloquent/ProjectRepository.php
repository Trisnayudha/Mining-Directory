<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProjectRepository implements ProjectRepositoryInterface
{
    protected $model;

    public function __construct(Project $model)
    {
        $this->model = $model;
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
        $query->join('company', 'company.id', '=', 'projects.company_id')
            ->leftJoin('project_category_list', 'project_category_list.project_id', '=', 'projects.id')
            ->leftJoin('md_category_company', 'project_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('project_sub_category_list', 'project_sub_category_list.project_id', '=', 'projects.id')
            ->leftJoin('md_sub_category_company', 'project_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        if (!empty($slug)) {
            $query->where('company.slug', $slug);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('projects.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('projects.description', 'LIKE', '%' . $search . '%');
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
            'projects.title',
            'projects.slug',
            'projects.image', // Get the asset from project_asset with asset_type png
            'company.company_name',
            'projects.views',
            'projects.download',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('projects.id', 'projects.title', 'projects.slug', 'projects.image', 'company.company_name', 'projects.views', 'company.package', 'projects.download')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }

    public function detail($slug)
    {
        $project = $this->model->newQuery()
            ->join('company', 'company.id', 'projects.company_id')
            ->where('projects.slug', $slug)
            ->select(
                'projects.id',
                'company.company_name',
                'company.package',
                'company.slug as company_slug',
                'company.image as company_image',
                'projects.title',
                'projects.views',
                'projects.download',
                'projects.slug',
                'projects.description',
                'projects.file',
                'projects.description',
            )->with(['projectCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        // Mengambil nama kategori
        if ($project && $project->projectCategories->isNotEmpty()) {
            $project->category_name = $project->projectCategories->first()->mdCategory->name;
            unset($project->projectCategories); // Opsional: Hapus data projectCategories yang tidak perlu
        }

        return $project;
    }
}

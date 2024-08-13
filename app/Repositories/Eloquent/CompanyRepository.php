<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Models\CompanyFavorite;
use App\Models\CompanyBusinessCard;
use App\Models\CompanyInquiry;
use App\Models\MediaResourceFavorite;
use App\Models\NewsFavorite;
use App\Models\ProductFavorite;
use App\Models\ProjectFavorite;
use App\Models\User;
use App\Models\VideosFavorite;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class CompanyRepository implements CompanyRepositoryInterface
{
    protected $model;
    protected $media;
    protected $news;
    protected $product;
    protected $project;
    protected $video;
    protected $companyFavorite;
    protected $companyBusinesscard;
    public function __construct(
        Company $model,
        CompanyFavorite $companyFavorite,
        CompanyBusinessCard $companyBusinesscard,
        MediaRepository $media,
        NewsRepository $news,
        ProductRepository $product,
        ProjectRepository $project,
        VideosRepository $video
    ) {
        $this->model = $model;
        $this->companyFavorite = $companyFavorite;
        $this->companyBusinesscard = $companyBusinesscard;
        $this->media = $media;
        $this->news = $news;
        $this->project = $project;
        $this->product = $product;
        $this->video = $video;
    }

    public function findHome()
    {
        return $this->model->where('package', 'platinum')->select('package', 'image', 'company_name', 'location', 'category_company', 'description', 'video', 'slug')->take(8)->get();
    }

    public function findList($request)
    {
        $package = $request->package;
        $paginate = $request->paginate ?? 10; // default 10
    }

    public function addFavorite($request, $userId)
    {
        $favoriteId = $request->input('favorite_id');
        $section = $request->input('section');

        if ($section == 'company') {
            $data = $this->favoriteCompany($favoriteId, $userId);
        } elseif ($section == 'product') {
            $data = $this->favoriteProduct($favoriteId, $userId);
        } elseif ($section == 'project') {
            $data = $this->favoriteProject($favoriteId, $userId);
        } elseif ($section == 'video') {
            $data = $this->favoriteVideo($favoriteId, $userId);
        } elseif ($section == 'news') {
            $data = $this->favoriteNews($favoriteId, $userId);
        } elseif ($section == 'media') {
            $data = $this->favoriteMedia($favoriteId, $userId);
        } else {
            $data = [
                'message' => 'Invalid section provided',
                'result' => []
            ];
        }

        return $data;
    }

    public function addBusinessCard($request, $userId)
    {
        $companyId = $request->input('company_id');

        // Insert the business card
        $this->companyBusinesscard->insert([
            'users_id' => $userId,
            'company_id' => $companyId,
            'status' => 'waiting',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return ['action' => 'inserted', 'company_id' => $companyId];
    }

    public function addInquiry($request, $userId)
    {
        $companyId = $request->input('company_id');
        $name = $request->input('name');
        $email = $request->input('email');
        $type = $request->input('type');
        $date = $request->input('date');
        $message = $request->input('message');

        // Buat instance baru dari CompanyInquiry dan simpan data
        $inquiry = new CompanyInquiry();
        $inquiry->company_id = $companyId;
        $inquiry->name = $name;
        $inquiry->email = $email;
        $inquiry->type = $type;
        $inquiry->date = $date;
        $inquiry->message = $message;
        $inquiry->users_id = $userId;
        $inquiry->status = 'waiting';

        // Simpan inquiry ke database
        if ($inquiry->save()) {
            return [
                'message' => 'Inquiry successfully added',
                'result' => $inquiry
            ];
        } else {
            return [
                'message' => 'Failed to add inquiry',
                'result' => null
            ];
        }
    }


    private function favoriteCompany($company_id, $userId)
    {
        $companyId = $company_id;

        // Attempt to find an existing favorite or create a new instance
        $favorite = $this->companyFavorite->firstOrNew([
            'users_id' => $userId,
            'company_id' => $companyId,
        ]);

        if ($favorite->exists) {
            // If it exists, delete the favorite
            $favorite->delete();

            return [
                'message' => 'Successfully removed favorite',
                'result' => ['action' => 'removed', 'company_id' => $companyId]
            ];
        } else {
            // If it does not exist, save the new favorite
            $favorite->save();

            return [
                'message' => 'Successfully added favorite',
                'result' => ['action' => 'added', 'company_id' => $companyId]
            ];
        }
    }

    private function favoriteProduct($product_id, $userId)
    {
        $productId = $product_id;

        // Attempt to find an existing favorite or create a new instance
        $favorite = ProductFavorite::firstOrNew([
            'users_id' => $userId,
            'product_id' => $productId,
        ]);

        if ($favorite->exists) {
            // If it exists, delete the favorite
            $favorite->delete();

            return [
                'message' => 'Successfully removed favorite',
                'result' => ['action' => 'removed', 'product_id' => $productId]
            ];
        } else {
            // If it does not exist, save the new favorite
            $favorite->save();

            return [
                'message' => 'Successfully added favorite',
                'result' => ['action' => 'added', 'product_id' => $productId]
            ];
        }
    }

    private function favoriteProject($project_id, $userId)
    {
        $projectId = $project_id;

        // Attempt to find an existing favorite or create a new instance
        $favorite = ProjectFavorite::firstOrNew([
            'users_id' => $userId,
            'project_id' => $projectId,
        ]);

        if ($favorite->exists) {
            // If it exists, delete the favorite
            $favorite->delete();

            return [
                'message' => 'Successfully removed favorite',
                'result' => ['action' => 'removed', 'project_id' => $projectId]
            ];
        } else {
            // If it does not exist, save the new favorite
            $favorite->save();

            return [
                'message' => 'Successfully added favorite',
                'result' => ['action' => 'added', 'project_id' => $projectId]
            ];
        }
    }

    private function favoriteMedia($media_resource_id, $userId)
    {
        $mediaResourceId = $media_resource_id;

        // Attempt to find an existing favorite or create a new instance
        $favorite = MediaResourceFavorite::firstOrNew([
            'users_id' => $userId,
            'media_resource_id' => $mediaResourceId,
        ]);

        if ($favorite->exists) {
            // If it exists, delete the favorite
            $favorite->delete();

            return [
                'message' => 'Successfully removed favorite',
                'result' => ['action' => 'removed', 'media_resource_id' => $mediaResourceId]
            ];
        } else {
            // If it does not exist, save the new favorite
            $favorite->save();

            return [
                'message' => 'Successfully added favorite',
                'result' => ['action' => 'added', 'media_resource_id' => $mediaResourceId]
            ];
        }
    }

    private function favoriteNews($news_id, $userId)
    {
        $newsId = $news_id;

        // Attempt to find an existing favorite or create a new instance
        $favorite = NewsFavorite::firstOrNew([
            'users_id' => $userId,
            'news_id' => $newsId,
        ]);

        if ($favorite->exists) {
            // If it exists, delete the favorite
            $favorite->delete();

            return [
                'message' => 'Successfully removed favorite',
                'result' => ['action' => 'removed', 'news_id' => $newsId]
            ];
        } else {
            // If it does not exist, save the new favorite
            $favorite->save();

            return [
                'message' => 'Successfully added favorite',
                'result' => ['action' => 'added', 'news_id' => $newsId]
            ];
        }
    }

    private function favoriteVideo($videos_id, $userId)
    {
        $videoId = $videos_id;

        // Attempt to find an existing favorite or create a new instance
        $favorite = VideosFavorite::firstOrNew([
            'users_id' => $userId,
            'video_id' => $videoId,
        ]);

        if ($favorite->exists) {
            // If it exists, delete the favorite
            $favorite->delete();

            return [
                'message' => 'Successfully removed favorite',
                'result' => ['action' => 'removed', 'video_id' => $videoId]
            ];
        } else {
            // If it does not exist, save the new favorite
            $favorite->save();

            return [
                'message' => 'Successfully added favorite',
                'result' => ['action' => 'added', 'video_id' => $videoId]
            ];
        }
    }


    public function findDetail($slug)
    {
        $query = $this->model->where('slug', $slug)->select(
            'company.id',
            'company.company_name',
            'company.package',
            'company.email_company',
            'company.phone_company',
            'company.website',
            'company.facebook',
            'company.instagram',
            'company.linkedin',
            'company.image',
            'company.banner_image',
            'company.verify_company',
            'company.slug'
        )->with(['companyCategories.mdCategory' => function ($query) {
            $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
        }])
            ->first();
        return $query;
    }

    public function findDetailSection($slug, $request)
    {
        // Mendapatkan data dari tabel company
        $section = $request->section;
        if ($section == 'company') {
            $data =  $this->DetailSectionCompany($slug);
        } elseif ($section == 'media') {
            $data = $this->media->findSearch($request);
        } elseif ($section == 'news') {
            $data = $this->news->findSearch($request);
        } elseif ($section == 'product') {
            $data = $this->product->findSearch($request);
        } elseif ($section == 'project') {
            $data = $this->project->findSearch($request);
        } elseif ($section == 'video') {
            $data = $this->video->findSearch($request);
        }

        return $data;
    }

    private function DetailSectionCompany($slug)
    {
        $query = $this->model->where('slug', $slug)->select(
            'company.id',
            'company.description',
            'company.video',
            'company.value_1',
            'company.value_2',
            'company.value_3'
        )->first();

        if ($query) {
            // Menggabungkan data address dan representative ke dalam $query
            $query->address = DB::table('company_address')->where('company_id', $query->id)->get();
            $query->representative = DB::table('company_representative')->where('company_id', $query->id)->get();

            // Load JSON data
            $jsonPath = public_path('countries+states+cities.json');
            $json = File::get($jsonPath);
            $data = json_decode($json, true);

            // Map IDs to names
            $query->address = $query->address->map(function ($address) use ($data) {
                // Find country name
                $country = collect($data)->firstWhere('id', $address->country);
                $address->country = $country['name'] ?? $address->country;

                // Find state name
                $state = collect($country['states'] ?? [])->firstWhere('id', $address->province);
                $address->province = $state['name'] ?? $address->province;

                // Find city name
                $city = collect($state['cities'] ?? [])->firstWhere('id', $address->city);
                $address->city = $city['name'] ?? $address->city;

                return $address;
            });
        }

        return $query;
    }



    public function findSearch($request)
    {
        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided
        $location = $request->location;
        $package = $request->package;

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

        // Filter by package if provided
        if (!empty($package)) {
            $query->where('company.package', '=', $package);
        }

        // Order results to prioritize search fields
        $query->orderByRaw("
            CASE
                WHEN company.company_name LIKE ? THEN 1
                WHEN company.description LIKE ? THEN 2
                WHEN company.location LIKE ? THEN 3
                ELSE 4
            END", [
            '%' . $search . '%',
            '%' . $search . '%',
            '%' . $search . '%',
            '%' . $search . '%',
            '%' . $search . '%',
            '%' . $location . '%'
        ]);

        $results = $query->groupBy(
            'company.image',
            'company.company_name',
            'company.description',
            'company.location',
            'company.video',
            'company.slug',
            'company.verify_company',
            'company.package'
        )->get([
            'company.image',
            'company.company_name',
            'company.description',
            'company.location',
            'company.video',
            'company.slug',
            'company.verify_company',
            'company.package',
            DB::raw('MIN(md_category_company.name) as category'), // Get the first category alphabetically
        ]);

        // Classify results into packages
        $platinum = $results->where('package', 'platinum');
        $gold = $results->where('package', 'gold');
        $silver = $results->where('package', 'silver');
        $free = $results->where('package', 'free');

        // Paginate each package
        $platinum_paginated = $this->paginateCollection($platinum, $paginate);
        $gold_paginated = $this->paginateCollection($gold, $paginate);
        $silver_paginated = $this->paginateCollection($silver, $paginate);
        $free_paginated = $this->paginateCollection($free, $paginate);

        // Prepare payload
        $payload = [
            'platinum' => $platinum_paginated->items(),
            'gold' => $gold_paginated->items(),
            'silver' => $silver_paginated->items(),
            'free' => $free_paginated->items(),
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

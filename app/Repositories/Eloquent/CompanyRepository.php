<?php

namespace App\Repositories\Eloquent;

use App\Helpers\LocationHelper;
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


    public function findDetail($slug, $id)
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
        if ($query) {
            $url = 'https://mining-directory.vercel.app/companies/detail/' . $slug;
            $query->share_links = [
                'web' => $url,
                'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url),
                'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($url),
                'instagram' => 'https://www.instagram.com/?url=' . urlencode($url), // Instagram tidak memiliki API khusus share, Anda bisa arahkan ke homepage
            ];
        }
        $findFavorite = null;
        if ($id) {
            $findFavorite = $this->companyFavorite->where('users_id', $id)->where('company_id', $query->id)->first();
        }
        $query->is_favorite = $findFavorite ? 1 : 0;
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

            // Menggunakan helper untuk mapping location IDs ke names
            // $query->address = LocationHelper::mapLocationIdsToNames($query->address);
        }

        return $query;
    }

    public function findSearch($request)
    {
        $search          = $request->search;
        $categoryParam   = $request->category_id;     // bisa ID atau nama
        $subCategoryParam = $request->sub_category_id; // bisa ID atau nama
        $paginate        = $request->paginate ?? 12;
        $location        = $request->location;
        $package         = $request->package;

        // base query: ambil 1 row per company (tanpa duplikasi)
        $query = $this->model->newQuery()
            ->from('company')
            ->select([
                'company.id',
                'company.image',
                'company.company_name',
                'company.description',
                'company.location',
                'company.video',
                'company.slug',
                'company.verify_company',
                'company.package',
            ]);

        // ambil 1 kategori (alfabet terkecil) via subquery -> tidak perlu GROUP BY
        $query->selectSub(function ($q) {
            $q->from('company_category_list as ccl')
                ->join('md_category_company as mc', 'mc.id', '=', 'ccl.category_id')
                ->whereColumn('ccl.company_id', 'company.id')
                ->selectRaw('MIN(mc.name)');
        }, 'category');

        // join hanya untuk filter (hindari duplikasi select) — gunakan exists subquery
        if (!empty($categoryParam)) {
            $query->whereExists(function ($q) use ($categoryParam) {
                $q->from('company_category_list as ccl')
                    ->join('md_category_company as mc', 'mc.id', '=', 'ccl.category_id')
                    ->whereColumn('ccl.company_id', 'company.id');

                if (is_numeric($categoryParam)) {
                    $q->where('mc.id', $categoryParam);
                } else {
                    $q->where('mc.name', 'LIKE', '%' . $categoryParam . '%');
                }
            });
        }

        if (!empty($subCategoryParam)) {
            $query->whereExists(function ($q) use ($subCategoryParam) {
                $q->from('company_sub_category_list as cscl')
                    ->join('md_sub_category_company as msc', 'msc.id', '=', 'cscl.sub_category_id')
                    ->whereColumn('cscl.company_id', 'company.id');

                if (is_numeric($subCategoryParam)) {
                    $q->where('msc.id', $subCategoryParam);
                } else {
                    $q->where('msc.name', 'LIKE', '%' . $subCategoryParam . '%');
                }
            });
        }

        // filter teks dasar
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('company.company_name', 'LIKE', $like)
                    ->orWhere('company.description', 'LIKE', $like)
                    ->orWhere('company.value_1', 'LIKE', $like)
                    ->orWhere('company.value_2', 'LIKE', $like)
                    ->orWhere('company.value_3', 'LIKE', $like);
            });
        }

        // lokasi
        if (!empty($location)) {
            $query->where('company.location', 'LIKE', '%' . $location . '%');
        }

        // package
        if (!empty($package)) {
            $query->where('company.package', $package);
        }

        // ranking relevansi — pasang hanya jika ada search
        if (!empty($search)) {
            $like = '%' . $search . '%';
            $query->orderByRaw("
            CASE
                WHEN company.company_name LIKE ? THEN 1
                WHEN company.description LIKE ? THEN 2
                ELSE 3
            END
        ", [$like, $like]);
        }

        // (opsional) urutkan package priority setelah relevansi
        $query->orderByRaw("FIELD(company.package, 'platinum','gold','silver','free')");

        // DISTINCT per company
        $query->distinct('company.id');

        // === PAGINATE DI LEVEL COMPANY ===
        $paginated = $query->paginate($paginate);

        // group hasil halaman saat ini ke paket
        $items = $paginated->getCollection();

        $grouped = [
            'platinum' => $items->where('package', 'platinum')->values(),
            'gold'     => $items->where('package', 'gold')->values(),
            'silver'   => $items->where('package', 'silver')->values(),
            'free'     => $items->where('package', 'free')->values(),
        ];

        // payload dengan meta paginate AKURAT (sesuai jumlah company)
        return [
            'platinum'     => $grouped['platinum'],
            'gold'         => $grouped['gold'],
            'silver'       => $grouped['silver'],
            'free'         => $grouped['free'],

            'current_page' => $paginated->currentPage(),
            'per_page'     => $paginated->perPage(),
            'last_page'    => $paginated->lastPage(),
            'from'         => $paginated->firstItem(),
            'to'           => $paginated->lastItem(),
            'total'        => $paginated->total(),     // total company yang match
        ];
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

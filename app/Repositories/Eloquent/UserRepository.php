<?php

namespace App\Repositories\Eloquent;

use App\Models\CompanyBusinessCard;
use App\Models\CompanyFavorite;
use App\Models\MediaResourceFavorite;
use App\Models\NewsFavorite;
use App\Models\ProductFavorite;
use App\Models\ProjectFavorite;
use App\Models\User;
use App\Models\VideosFavorite;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UserRepository implements UserRepositoryInterface
{
    protected $model;
    protected $companyBusinessCard;
    protected $companyFavorite;

    public function __construct(
        User $model,
        CompanyBusinessCard $companyBusinessCard,
        CompanyFavorite $companyFavorite
    ) {
        $this->model = $model;
        $this->companyBusinessCard = $companyBusinessCard;
        $this->companyFavorite = $companyFavorite;
    }

    public function findByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function createUsers(array $data)
    {
        return User::create($data);
    }

    public function getDetail($id)
    {
        return $this->model->newQuery()->where('id', $id)->first();
    }

    public function editProfile($request, $id)
    {
        $user = $this->model->find($id);

        if (!$user) {
            return null;
        }

        $user->name = $request->input('name');
        $user->job_title = $request->input('job_title');
        $user->company_name = $request->input('company_name');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file) {
                $imageName = time() . '.' . $file->extension();
                $dbPath = 'storage/profile/' . $imageName;
                $saveFolder = $file->storeAs('public/profile', $imageName);

                // Create a new Intervention Image instance from the uploaded file
                $compressedImage = Image::make(storage_path('app/public/profile/' . $imageName));

                // Resize the image to a maximum width of 400 while maintaining aspect ratio
                $compressedImage->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Save the resized image
                $compressedImage->save(storage_path('app/public/profile/' . $imageName));
                $user->image = url($dbPath);
            }
        }

        $user->save();

        return $user;
    }

    public function editProfileDetail($request, $id)
    {
        $user = $this->model->find($id);

        if (!$user) {
            return null;
        }

        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return $user;
    }

    public function editProfileBio($request, $id)
    {
        $user = $this->model->find($id);
        if (!$user) {
            return null;
        }

        $user->about = $request->about;
        $user->save();

        return $user;
    }

    public function editProfileBackground($request, $id)
    {
        $user = $this->model->find($id);
        if (!$user) {
            return null;
        }

        if ($request->hasFile('background_image')) {
            $file = $request->file('background_image');
            if ($file) {
                $imageName = time() . '.' . $file->extension();
                $dbPath = 'storage/profile-background/' . $imageName;
                $saveFolder = $file->storeAs('public/profile-background', $imageName);

                // Create a new Intervention Image instance from the uploaded file
                $compressedImage = Image::make(storage_path('app/public/profile-background/' . $imageName));

                // Resize the image to a maximum width of 400 while maintaining aspect ratio
                $compressedImage->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Save the resized image
                $compressedImage->save(storage_path('app/public/profile-background/' . $imageName));
                $user->background_image = url($dbPath);
            }
        }
        $user->save();

        return $user;
    }

    public function getBusinessCard($request, $id)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $paginate = $request->input('paginate', 10); // Default pagination value

        $query = $this->companyBusinessCard->where('users_id', $id)
            ->leftJoin('company', 'company_users_buisnesscard.company_id', '=', 'company.id')
            ->leftJoin('company_category_list', function ($join) {
                $join->on('company_users_buisnesscard.company_id', '=', 'company_category_list.company_id')
                    ->whereRaw('company_category_list.id = (select min(id) from company_category_list where company_category_list.company_id = company_users_buisnesscard.company_id)');
            })
            ->leftJoin('md_category_company', 'company_category_list.category_id', '=', 'md_category_company.id')
            ->select(
                'company_users_buisnesscard.*',
                'company.company_name',
                'md_category_company.name as category_name'
            );

        // Apply search filter on company name
        if (!empty($search)) {
            $query->where('company.company_name', 'like', '%' . $search . '%');
        }

        // Apply status filter on company business card
        if (!empty($filter)) {
            $query->where('company_users_buisnesscard.status', $filter);
        }

        // Paginate results
        $results = $query->groupBy(
            'company_users_buisnesscard.id',
            'company.company_name',
            'md_category_company.name',
            'company_users_buisnesscard.created_at',
            'company_users_buisnesscard.updated_at',
            'company_users_buisnesscard.company_id',
            'company_users_buisnesscard.users_id',
            'company_users_buisnesscard.status'
        ) // Group by to ensure single category is returned
            ->paginate($paginate);

        // Map results to desired format
        $formattedResults = $results->map(function ($businessCard, $index) {
            return [
                'nomor' => $index + 1,
                'company_name' => $businessCard->company_name,
                'company_category' => $businessCard->category_name,
                'date_sent' => $businessCard->created_at->format('Y-m-d H:i:s'),
                'status' => $businessCard->status,
            ];
        });

        return [
            'data' => $formattedResults,
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ]
        ];
    }


    public function getFavorite($request, $id)
    {
        $section = $request->section;
        if ($section == 'company') {
            $data = $this->favoriteCompany($id);
        } elseif ($section == 'product') {
            $data = $this->favoriteProduct($id);
        } elseif ($section == 'project') {
            $data = $this->favoriteProject($id);
        } elseif ($section == 'video') {
            $data = $this->favoriteVideo($id);
        } elseif ($section == 'news') {
            $data = $this->favoriteNews($id);
        } elseif ($section == 'media') {
            $data = $this->favoriteMedia($id);
        } else {
            $data = [
                'message' => 'Invalid section provided',
                'result' => []
            ];
        }
        return $data;
    }

    public function changePassword($request, $userId)
    {
        $user = $this->model->find($userId);

        if (!$user) {
            return ['message' => 'User not found', 'status' => 404];
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return ['message' => 'Validation failed', 'errors' => $validator->errors(), 'status' => 400];
        }

        // Check if the old password matches
        if (!Hash::check($request->input('old_password'), $user->password)) {
            return ['message' => 'Old password is incorrect', 'status' => 400];
        }

        // Update the password
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return ['message' => 'Password successfully changed', 'status' => 200];
    }


    private function favoriteCompany($id)
    {
        $query = $this->companyFavorite->join('company', 'company.id', 'company_users_favorite.company_id')->select(
            'company.id as company_id',
            'company_users_favorite.id as favorite_id',
            'company.company_name',
            'company.image',
            'company.location',
            'company.slug'
        )->where('users_id', $id)->get();
        return $query;
    }

    private function favoriteProduct($id)
    {
        $query = ProductFavorite::join('products', 'products.id', '=', 'products_favorite.product_id')
            ->leftJoin('products_asset', function ($join) {
                $join->on('products_asset.product_id', '=', 'products.id')
                    ->where('products_asset.asset_type', '=', 'png');
            })
            ->join('company', 'company.id', '=', 'products.company_id')
            ->select(
                'products.id as product_id',
                'products_favorite.id as favorite_id',
                'products.title',
                'products.slug',
                'products_asset.asset as image', // Get the asset from products_asset with asset_type png
                'company.company_name',
            )
            ->where('users_id', $id)
            ->get();

        return $query;
    }


    private function favoriteProject($id)
    {
        $query = ProjectFavorite::join('projects', 'projects.id', '=', 'projects_favorite.project_id')
            ->join('company', 'company.id', '=', 'projects.company_id')
            ->select(
                'projects.id as project_id',
                'projects_favorite.id as favorite_id',
                'projects.title',
                'projects.slug',
                'projects.image',
                'company.company_name'
            )->where('users_id', $id)->get();

        return $query;
    }

    private function favoriteVideo($id)
    {
        $query = VideosFavorite::join('videos', 'videos.id', '=', 'videos_favorite.video_id')
            ->join('company', 'company.id', '=', 'videos.company_id')
            ->select(
                'videos.id as videos_id',
                'videos_favorite.id as favorite_id',
                'videos.title',
                'videos.slug',
                'videos.asset',
                'company.company_name'
            )->where('users_id', $id)->get();

        return $query;
    }

    private function favoriteNews($id)
    {
        $query = NewsFavorite::join('news', 'news.id', '=', 'news_favorite.news_id')
            ->join('company', 'company.id', '=', 'news.company_id')
            ->select(
                'news.id as news_id',
                'news_favorite.id as favorite_id',
                'news.title',
                'news.image',
                'news.date_news',
                'company.company_name',
                'news.slug'
            )->where('users_id', $id)->get();

        return $query;
    }

    private function favoriteMedia($id)
    {
        $query = MediaResourceFavorite::join('media_resource', 'media_resource.id', '=', 'media_resource_favorite.media_resource_id')
            ->join('company', 'company.id', '=', 'media_resource.company_id')
            ->select(
                'media_resource.id as media_resource_id',
                'media_resource_favorite.id as favorite_id',
                'media_resource.title',
                'media_resource.image',
                'media_resource.slug',
                'company.company_name'
            )->where('users_id', $id)->get();

        return $query;
    }
}

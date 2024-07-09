<?php

namespace App\Repositories\Eloquent;

use App\Models\CompanyBusinessCard;
use App\Models\CompanyFavorite;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
        }
        return $data;
    }

    private function favoriteCompany($id)
    {
        $query = $this->companyFavorite->join('company', 'company.id', 'company_users_favorite.company_id')->select(
            'company.id as company_id',
            'company_users_favorite.id as favorite_id',
            'company.company_name',
            'company.image',
            'company.location'
        )->where('users_id', $id)->get();
        return $query;
    }
}

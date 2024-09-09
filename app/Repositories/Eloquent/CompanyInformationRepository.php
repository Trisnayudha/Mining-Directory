<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Models\Example;
use App\Models\Product;
use App\Repositories\Contracts\CompanyInformationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;


class CompanyInformationRepository implements CompanyInformationRepositoryInterface
{
    protected $model;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }

    public function detail($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function store($id, $request)
    {
        $user = $this->model->where('id', $id)->first();

        // Mengecualikan password dari data yang akan di-update
        $data = $request->except('password');
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file) {
                $imageName = time() . '.' . $file->extension();
                $dbPath = 'storage/company/' . $imageName;
                $saveFolder = $file->storeAs('public/company', $imageName);

                // Create a new Intervention Image instance from the uploaded file
                $compressedImage = Image::make(storage_path('app/public/company/' . $imageName));

                // Resize the image to a maximum width of 400 while maintaining aspect ratio
                $compressedImage->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Save the resized image
                $compressedImage->save(storage_path('app/public/company/' . $imageName));
                $data['image'] = url($dbPath);
            }
        }
        if ($request->hasFile('banner_image')) {
            $banner_image = $request->file('banner_image');
            if ($banner_image) {
                $imageName = time() . '.' . $banner_image->extension();
                $dbPathBanner = 'storage/company-banner/' . $imageName;
                $saveFolder = $banner_image->storeAs('public/company-banner', $imageName);

                // Create a new Intervention Image instance from the uploaded file
                $compressedImage = Image::make(storage_path('app/public/company-banner/' . $imageName));

                // Resize the image to a maximum width of 400 while maintaining aspect ratio
                $compressedImage->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Save the resized image
                $compressedImage->save(storage_path('app/public/company-banner/' . $imageName));
                $data['banner_image'] = url($dbPathBanner);
            }
        }
        // Update data user tanpa password
        $user->update($data);

        return $user;
    }
}

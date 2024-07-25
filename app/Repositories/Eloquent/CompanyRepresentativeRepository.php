<?php

namespace App\Repositories\Eloquent;

use App\Models\CompanyRepresentative;
use App\Repositories\Contracts\CompanyRepresentativeRepositoryInterface;
use Intervention\Image\Facades\Image;

class CompanyRepresentativeRepository implements CompanyRepresentativeRepositoryInterface
{
    protected $model;

    public function __construct(CompanyRepresentative $model)
    {
        $this->model = $model;
    }

    public function index($companyId)
    {
        return $this->model->where('company_id', $companyId)->get();
    }

    public function store($companyId, $payload)
    {
        $payload['company_id'] = $companyId;

        if (isset($payload['image'])) {
            $payload['image'] = $this->handleImageUpload($payload['image']);
        }

        return $this->model->create($payload);
    }

    public function update($id, $payload)
    {
        $representative = $this->model->find($id);

        if ($representative) {
            if (isset($payload['image'])) {
                $payload['image'] = $this->handleImageUpload($payload['image']);
            }

            $representative->update($payload);
            return $representative;
        }

        return null;
    }

    public function delete($id)
    {
        $representative = $this->model->find($id);

        if ($representative) {
            $representative->delete();
            return true;
        }

        return false;
    }

    private function handleImageUpload($file)
    {
        $imageName = time() . '.' . $file->extension();
        $dbPath = 'storage/representative-profile/' . $imageName;
        $file->storeAs('public/representative-profile', $imageName);

        // Create a new Intervention Image instance from the uploaded file
        $compressedImage = Image::make(storage_path('app/public/representative-profile/' . $imageName));

        // Resize the image to a maximum width of 400 while maintaining aspect ratio
        $compressedImage->resize(400, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Save the resized image
        $compressedImage->save(storage_path('app/public/representative-profile/' . $imageName));

        return url($dbPath);
    }
}

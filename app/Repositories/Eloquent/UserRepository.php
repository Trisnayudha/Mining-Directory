<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
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
}

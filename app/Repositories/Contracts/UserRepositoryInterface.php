<?php

// app/Repositories/Contracts/UserRepositoryInterface.php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findByEmail($email);

    public function createUsers(array $data);

    public function getDetail($id);

    public function editProfile($request, $id);

    public function editProfileDetail($request, $id);

    public function getBusinessCard($request, $id);

    public function getFavorite($request, $id);

    public function editProfileBio($request, $id);

    public function editProfileBackground($request, $id);
}

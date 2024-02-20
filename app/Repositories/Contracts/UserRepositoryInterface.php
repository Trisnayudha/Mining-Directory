<?php

// app/Repositories/Contracts/UserRepositoryInterface.php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findByEmail($email);

    public function createUsers(array $data);
}

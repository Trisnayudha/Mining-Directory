<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface MediaRepositoryInterface
{
    public function findSearch($request);
    public function detail($slug);
}

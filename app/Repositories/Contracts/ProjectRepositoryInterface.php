<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface ProjectRepositoryInterface
{
    public function findSearch($request);
    public function detail($slug);
}

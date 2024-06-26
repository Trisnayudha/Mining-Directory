<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface VideosRepositoryInterface
{
    public function findSearch($request);
    public function detail($slug);
}

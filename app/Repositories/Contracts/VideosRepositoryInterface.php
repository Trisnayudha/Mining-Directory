<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface VideosRepositoryInterface
{
    public function findHome();
    public function findSearch($request);
    public function detail($slug);
}

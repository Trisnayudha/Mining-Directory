<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface NewsRepositoryInterface
{
    public function findHome();
    public function findSearch($request);
    public function detail($slug);
}

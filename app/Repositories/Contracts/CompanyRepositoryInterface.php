<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface CompanyRepositoryInterface
{
    public function findList($limit);
    public function findDetail($slug);
    public function findSearch($search);
}

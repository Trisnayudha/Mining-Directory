<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface CategoryRepositoryInterface
{
    public function findAll();
    public function popular();
}

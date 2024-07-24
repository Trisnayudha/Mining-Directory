<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface CompanyInformationRepositoryInterface
{
    public function detail($id);
    public function store($id, $request);
}

<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface CompanyAddressRepositoryInterface
{
    public function index($id);
    public function store($id, $payload);
    public function update($id, $payload);
    public function delete($id);
}

<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface CompanyRepresentativeRepositoryInterface
{
    public function index($id);
    public function store($id, $payload);
    public function update($id, $payload);
    public function delete($id);
}

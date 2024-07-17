<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function card($id, $request);
    public function listVisitor($id, $request);
}

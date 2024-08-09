<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function card($id, $request);
    public function listVisitor($id, $request);
    public function listInquiry($id, $request);
    public function approveInquiry($request);
    public function listBusinessCard($id, $request);
    public function visitAnalyst($id, $request);
    public function assetAnalyst($id, $request);
    public function checkCompany($id);
}

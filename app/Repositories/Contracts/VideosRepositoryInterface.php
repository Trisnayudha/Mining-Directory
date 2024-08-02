<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface VideosRepositoryInterface
{
    public function findHome();
    public function findSearch($request);
    public function detail($slug);

    public function cIndex($companyId);
    public function cStore($companyId, $request);
    public function cEdit($companyId, $slug);
    public function cDestroy($companyId, $slug);
    public function cUpdate($companyId, $request);
    public function cListing($companyId, $slug);
}

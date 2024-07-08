<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface CompanyRepositoryInterface
{
    public function findList($limit);
    public function findDetail($slug);
    public function findDetailSection($slug, $section);
    public function findSearch($search);
    public function addFavorite($request, $id);
    public function addBusinessCard($request, $id);
    public function addInquiry($request, $id);
}

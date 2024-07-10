<?php

// app/Repositories/Contracts/CompanyRepositoryInterface.php

namespace App\Repositories\Contracts;

interface HelpRepositoryInterface
{
    public function faqHome($request);
    public function faqProfile($request);
    public function contactUs($request, $id);
    public function privacy();
    public function term();
}

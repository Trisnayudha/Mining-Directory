<?php

namespace App\Traits;

use App\Models\CompanyLog;

trait CompanyLogTrait
{
    public function logCompanyDetail($companyId, $userId)
    {
        CompanyLog::create([
            'company_id' => $companyId,
            'users_id' => $userId
        ]);
    }
}

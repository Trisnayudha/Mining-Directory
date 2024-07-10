<?php

namespace App\Jobs;

use App\Models\ActivityLog;

class LogUserActivityJob extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        ActivityLog::create($this->data);
    }
}

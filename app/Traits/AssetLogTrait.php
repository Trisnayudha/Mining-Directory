<?php

namespace App\Traits;

use App\Models\MediaResourceLog;
use App\Models\NewsLog;
use App\Models\ProductLog;
use App\Models\ProjectLog;
use App\Models\VideosLog;

trait AssetLogTrait
{
    public function logProductDetail($assetId, $userId)
    {
        ProductLog::create([
            'product_id' => $assetId,
            'users_id' => $userId
        ]);
    }
    public function logProjectDetail($assetId, $userId)
    {
        ProjectLog::create([
            'project_id' => $assetId,
            'users_id' => $userId
        ]);
    }
    public function logNewsDetail($assetId, $userId)
    {
        NewsLog::create([
            'news_id' => $assetId,
            'users_id' => $userId
        ]);
    }
    public function logVideosDetail($assetId, $userId)
    {
        VideosLog::create([
            'video_id' => $assetId,
            'users_id' => $userId
        ]);
    }
    public function logMediaDetail($assetId, $userId)
    {
        MediaResourceLog::create([
            'media_resource_id' => $assetId,
            'users_id' => $userId
        ]);
    }
}

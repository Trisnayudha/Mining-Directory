<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\NewsRepository;
use App\Traits\AssetLogTrait;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewsController extends Controller
{
    use ResponseHelper, AssetLogTrait; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $news;
    public function __construct(NewsRepository $news)
    {
        $this->news = $news;
    }

    public function detail($slug)
    {
        $userId = null;
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
        } catch (\Exception $e) {
            // Token tidak ada atau tidak valid, biarkan $userId tetap null
        }
        $data = $this->news->detail($slug);
        if ($data && $userId) {
            $this->logNewsDetail($data->id, $userId);
        }
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

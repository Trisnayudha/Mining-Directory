<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\VideosRepository;
use App\Traits\AssetLogTrait;
use Tymon\JWTAuth\Facades\JWTAuth;

class VideoController extends Controller
{
    use ResponseHelper, AssetLogTrait; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $videos;
    public function __construct(VideosRepository $videos)
    {
        $this->videos = $videos;
    }

    public function detail($slug)
    {
        $userId = null;
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
        } catch (\Exception $e) {
            // Token tidak ada atau tidak valid, biarkan $userId tetap null
        }
        $data = $this->videos->detail($slug);
        if ($data && $userId) {
            $this->logVideosDetail($data->id, $userId);
        }
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

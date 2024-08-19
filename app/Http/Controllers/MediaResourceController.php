<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\MediaRepository;
use App\Traits\AssetLogTrait;
use Tymon\JWTAuth\Facades\JWTAuth;

class MediaResourceController extends Controller
{
    use ResponseHelper, AssetLogTrait; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $media;
    public function __construct(MediaRepository $media)
    {
        $this->media = $media;
    }

    public function detail($slug)
    {
        $userId = null;
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
        } catch (\Exception $e) {
            // Token tidak ada atau tidak valid, biarkan $userId tetap null
        }
        $data = $this->media->detail($slug);
        if ($data && $userId) {
            $this->logMediaDetail($data->id, $userId);
        }
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function download($slug)
    {
        $data = $this->media->download($slug);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

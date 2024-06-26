<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\VideosRepository;

class VideoController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
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
        $data = $this->videos->detail($slug);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

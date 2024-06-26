<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\MediaRepository;

class MediaResourceController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
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
        $data = $this->media->detail($slug);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\NewsRepository;

class NewsController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
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
        $data = $this->news->detail($slug);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

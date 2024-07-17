<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Models\Company;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\HomeRepository;
use App\Repositories\Eloquent\NewsRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\VideosRepository;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $category;
    protected $company;
    protected $product;
    protected $video;
    protected $news;
    protected $home;
    public function __construct(
        CategoryRepository $category,
        CompanyRepository $company,
        ProductRepository $product,
        VideosRepository $video,
        NewsRepository $news,
        HomeRepository $home
    ) {
        $this->category = $category;
        $this->company = $company;
        $this->product = $product;
        $this->video = $video;
        $this->news = $news;
        $this->home = $home;
    }

    public function carousel()
    {
        $data = $this->home->carousel();
        return $this->sendResponse('Successfully retrieved carousel data', $data, 200);
    }

    public function statistic()
    {
        $data = $this->home->statistic();
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function category()
    {
        $data = $this->category->findAll();
        return $this->sendResponse('Successfully retrieved category data', $data, 200);
    }

    public function popularCategory()
    {
        $data = $this->category->popular();
        return $this->sendResponse('Successfully retrieved category data', $data, 200);
    }

    public function company()
    {
        $data = $this->company->findHome();
        return $this->sendResponse('Successfully show company data', $data, 200);
    }

    public function product()
    {
        $data = $this->product->findHome();
        return $this->sendResponse('Successfully show products data', $data, 200);
    }

    public function video()
    {
        $data = $this->video->findHome();
        return $this->sendResponse('Successfully show videos data', $data, 200);
    }

    public function news()
    {
        $data = $this->news->findHome();
        return $this->sendResponse('Successfully show news data', $data, 200);
    }
}

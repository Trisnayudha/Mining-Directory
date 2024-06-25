<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Models\Company;
use App\Repositories\Eloquent\CategoryRepository;
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
    public function __construct(CategoryRepository $category)
    {
        $this->category = $category;
    }

    public function carousel()
    {
        $data = [
            [
                'image' => 'https://dummyimage.com/1000x350/eb26eb/ffffff',
                'slug' => 'explore-mining-gear-1',
            ],
            [
                'image' => 'https://dummyimage.com/1000x350/452045/ffffff',
                'slug' => 'explore-mining-gear-2',
            ],
            [
                'image' => 'https://dummyimage.com/1000x350/147dd9/ffffff',
                'slug' => 'explore-mining-gear-3',
            ],
            [
                'image' => 'https://dummyimage.com/1000x350/25f5eb/ffffff',
                'slug' => 'explore-mining-gear-4',
            ],
            [
                'image' => 'https://dummyimage.com/1000x350/f5c827/ffffff',
                'slug' => 'explore-mining-gear-5',
            ],
        ];

        return $this->sendResponse('Successfully retrieved carousel data', $data, 200);
    }

    public function statistic()
    {
        $data = [
            'data_1' => 300,
            'data_2' => 10000,
            'data_3' => 1500
        ];

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
        $data = Company::where('package', 'platinum')->select('package', 'image', 'company_name', 'location', 'category_company', 'description', 'video', 'slug')->take(8)->get();
        return $this->sendResponse('Successfully show company data', $data, 200);
    }

    public function product()
    {
        $data = DB::table('products')->join('company', 'company.id', 'products.company_id')->select('products.*', 'company.company_name')->take(4)->get();
        return $this->sendResponse('Successfully show products data', $data, 200);
    }

    public function video()
    {
        $data = DB::table('videos')->join('company', 'company.id', 'videos.company_id')->select('videos.*', 'company.company_name')->take(4)->get();
        return $this->sendResponse('Successfully show videos data', $data, 200);
    }

    public function news()
    {
        $data = DB::table('news')->join('company', 'company.id', 'news.company_id')->select('news.*', 'company.company_name')->take(5)->get();
        return $this->sendResponse('Successfully show news data', $data, 200);
    }
}

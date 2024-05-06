<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $data = [
            [
                'title' => 'Batteries',
                'image' => 'https://dummyimage.com/300x200/000/fff&text=Batteries',
                'slug'  => 'batteries-category',
                'items' => 120,
            ],
            [
                'title' => 'Assay Equipment',
                'image' => 'https://dummyimage.com/300x200/111/fff&text=Assay+Equipment',
                'slug'  => 'assay-equipment-category',
                'items' => 120,
            ],
            [
                'title' => 'Bucket Wheel Excavators',
                'image' => 'https://dummyimage.com/300x200/222/fff&text=Bucket+Wheel+Excavators',
                'slug'  => 'bucket-wheel-excavators-category',
                'items' => 120,
            ],
            [
                'title' => 'Environment Management',
                'image' => 'https://dummyimage.com/300x200/333/fff&text=Environment+Management',
                'slug'  => 'environment-management-category',
                'items' => 120,
            ],
            [
                'title' => 'Construction Equipment',
                'image' => 'https://dummyimage.com/300x200/444/fff&text=Construction+Equipment',
                'slug'  => 'construction-equipment-category',
                'items' => 120,
            ],
            [
                'title' => 'Engineering Service',
                'image' => 'https://dummyimage.com/300x200/555/fff&text=Engineering+Service',
                'slug'  => 'engineering-service-category',
                'items' => 120,
            ],
        ];

        return $this->sendResponse('Successfully retrieved category data', $data, 200);
    }

    public function company()
    {
        $data = DB::table('company')->where('package', 'platinum')->select('package', 'image', 'company_name', 'location', 'category_company', 'description', 'video', 'slug')->take(8)->get();
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

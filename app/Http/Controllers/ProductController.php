<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\ProductRepository;

class ProductController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $product;
    public function __construct(ProductRepository $product)
    {
        $this->product = $product;
    }

    public function detail($slug)
    {
        $data = $this->product->detail($slug);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

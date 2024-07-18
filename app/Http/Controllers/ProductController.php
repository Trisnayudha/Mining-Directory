<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\ProductRepository;
use App\Traits\AssetLogTrait;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
{
    use ResponseHelper, AssetLogTrait; // Gunakan trait di sini
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
        $userId = null;
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
        } catch (\Exception $e) {
            // Token tidak ada atau tidak valid, biarkan $userId tetap null
        }
        $data = $this->product->detail($slug);
        if ($data && $userId) {
            $this->logProductDetail($data->id, $userId);
        }
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

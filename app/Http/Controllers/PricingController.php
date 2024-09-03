<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\PriceRepository;

class PricingController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $price;
    public function __construct(PriceRepository $price)
    {
        $this->price = $price;
    }

    public function index()
    {
        $data = $this->price->getPrice();
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

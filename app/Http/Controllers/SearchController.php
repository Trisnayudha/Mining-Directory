<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\SearchRepository;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $search;
    public function __construct(SearchRepository $search)
    {
        $this->search = $search;
    }

    public function index(Request $request)
    {
        $data = $this->search->search($request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

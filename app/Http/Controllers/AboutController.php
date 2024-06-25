<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;

class AboutController extends Controller
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

    public function index()
    {
        $data = [
            'wording' => 'The Mining Directory is the first online and printed directory designed specifically to Indonesia and global mining community. This directory is a perfect way to get discovered by local and international mining community.'
        ];

        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

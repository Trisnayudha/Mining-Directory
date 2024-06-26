<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\ProjectRepository;

class ProjectController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $project;
    public function __construct(ProjectRepository $project)
    {
        $this->project = $project;
    }

    public function detail($slug)
    {
        $data = $this->project->detail($slug);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

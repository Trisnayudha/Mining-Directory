<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\ProjectRepository;
use App\Traits\AssetLogTrait;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProjectController extends Controller
{
    use ResponseHelper, AssetLogTrait; // Gunakan trait di sini
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
        $userId = null;
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
        } catch (\Exception $e) {
            // Token tidak ada atau tidak valid, biarkan $userId tetap null
        }
        $data = $this->project->detail($slug, $userId);
        if ($data && $userId) {
            $this->logProjectDetail($data->id, $userId);
        }
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function more(Request $request)
    {
        $data = $this->project->moreList($request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function related(Request $request)
    {
        $data = $this->project->relatedList($request);
        return $this->sendResponse('Successfully show data', $data, 200);
    }

    public function download($slug)
    {
        $data = $this->project->download($slug);
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

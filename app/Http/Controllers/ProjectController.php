<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Repositories\Eloquent\ProjectRepository;
use App\Traits\AssetLogTrait;
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
        $data = $this->project->detail($slug);
        if ($data && $userId) {
            $this->logProjectDetail($data->id, $userId);
        }
        return $this->sendResponse('Successfully show data', $data, 200);
    }
}

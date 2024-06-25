<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\SearchRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class SearchRepository implements SearchRepositoryInterface
{
    protected $company;
    protected $product;
    protected $media;
    protected $project;
    protected $video;
    protected $news;

    public function __construct(
        CompanyRepository $company,
        ProductRepository $product,
        MediaRepository $media,
        ProjectRepository $project,
        VideosRepository $video,
        NewsRepository $news
    ) {
        $this->company = $company;
        $this->product = $product;
        $this->media = $media;
        $this->project = $project;
        $this->video = $video;
        $this->news = $news;
    }
    public function search($request)
    {
        $asset = $request->asset;
        if ($asset == 'Company') {
            $data = $this->company->findSearch($request);
        } elseif ($asset == 'Product') {
            $data = $this->product->findSearch($request);
        } elseif ($asset == 'Media') {
            $data = $this->media->findSearch($request);
        } elseif ($asset == 'Project') {
            $data = $this->project->findSearch($request);
        } elseif ($asset == 'Videos') {
            $data = $this->video->findSearch($request);
        } elseif ($asset == 'News') {
            $data = $this->news->findSearch($request);
        }
        return $data ?? $asset;
    }
}

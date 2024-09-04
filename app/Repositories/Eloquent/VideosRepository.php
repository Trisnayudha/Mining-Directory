<?php

namespace App\Repositories\Eloquent;

use App\Models\Videos;
use App\Models\VideosCategory;
use App\Models\VideosFavorite;
use App\Repositories\Contracts\VideosRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideosRepository implements VideosRepositoryInterface
{
    protected $videos;
    protected $videosCategory;
    protected $videosFavorite;
    public function __construct(Videos $videos, VideosCategory $videosCategory, VideosFavorite $videosFavorite)
    {
        $this->videos = $videos;
        $this->videosCategory = $videosCategory;
        $this->videosFavorite = $videosFavorite;
    }


    public function findHome()
    {
        return $this->videos->join('company', 'company.id', 'videos.company_id')->select('videos.*', 'company.company_name')->take(4)->get();
    }
    public function findSearch($request)
    {
        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided
        $slug = $request->slug;

        $query = $this->videos->newQuery();
        // Join with category and subcategory tables
        $query->join('company', 'company.id', '=', 'videos.company_id')
            ->leftJoin('videos_category_list', 'videos_category_list.videos_id', '=', 'videos.id')
            ->leftJoin('md_category_company', 'videos_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('videos_sub_category_list', 'videos_sub_category_list.videos_id', '=', 'videos.id')
            ->leftJoin('md_sub_category_company', 'videos_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        if (!empty($slug)) {
            $query->where('company.slug', $slug);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('videos.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('videos.description', 'LIKE', '%' . $search . '%');
            });
        }
        // Filter by category name if provided
        if (!empty($category_name)) {
            $query->where('md_category_company.name', 'LIKE', '%' . $category_name . '%');
        }
        // Filter by sub-category name if provided
        if (!empty($sub_category_name)) {
            $query->where('md_sub_category_company.name', 'LIKE', '%' . $sub_category_name . '%');
        }
        // Select the required columns and group by product.id
        $results = $query->select([
            'videos.title',
            'videos.slug',
            'videos.asset', // Get the asset from project_asset with asset_type png
            'company.company_name',
            'videos.views',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('videos.id', 'videos.title', 'videos.slug', 'videos.asset', 'company.company_name', 'videos.views', 'company.package')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }

    public function detail($slug, $id)
    {
        $video = $this->videos->newQuery()
            ->join('company', 'company.id', 'videos.company_id')
            ->where('videos.slug', $slug)
            ->select(
                'videos.id',
                'company.company_name',
                'company.slug as company_slug',
                'company.package',
                'company.image as company_image',
                'videos.title',
                'videos.slug',
                'videos.asset',
                'videos.views',
                'videos.description',
            )
            ->with(['videoCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        // Mengambil nama kategori
        if ($video && $video->videoCategories->isNotEmpty()) {
            $video->category_name = $video->videoCategories->first()->mdCategory->name;
            unset($video->videoCategories); // Opsional: Hapus data videoCategories yang tidak perlu
        }

        // Menambahkan URL Share
        if ($video) {
            $url = 'https://mining-directory.vercel.app/video/detail/' . $slug;
            $video->share_links = [
                'web' => $url,
                'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url),
                'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($url),
                'instagram' => 'https://www.instagram.com/?url=' . urlencode($url), // Instagram tidak memiliki API khusus share, Anda bisa arahkan ke homepage
            ];
        }
        $findFavorite = null;
        if ($id) {
            $findFavorite = $this->videosFavorite->where('users_id', $id)->where('video_id', $video->id)->first();
        }
        $video->isFavorite = $findFavorite ? 1 : 0;
        return $video;
    }

    public function moreList($id)
    {
        return  $this->videos->where('id', '!=', $id)->select('id', 'asset', 'title', 'views', 'created_at', 'slug')->take(4)->get();
    }

    public function cIndex($companyId)
    {
        return  $this->videos->where('company_id', $companyId)
            ->with(['videoCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])->select('id', 'title', 'slug', 'asset', 'status', 'views')->orderby('id', 'desc')->get();
    }

    public function cStore($companyId, $request)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Simpan data proyek
            $videosData = $request->only([
                'title',
                'description',
                'asset',
                'status'
            ]);

            // Buat slug berdasarkan title
            $videosData['slug'] = Str::slug($request->input('title') . '-' . time());
            $videosData['company_id'] = $companyId;
            $videosData['views'] = 0;
            // Simpan file PDF, jika ada
            $videos = $this->videos->create($videosData);

            // Simpan data categories
            if ($request->has('categories')) {
                foreach ($request->categories as $categoryId) {
                    $this->videosCategory->create([
                        'videos_id' => $videos->id,
                        'category_id' => $categoryId
                    ]);
                }
            }

            // Commit transaksi
            DB::commit();

            return $videos;
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            throw $e;
        }
    }

    public function cUpdate($productId, $request)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Temukan produk
            $product = $this->videos->findOrFail($productId);

            // Update data produk
            $productData = $request->only([
                'title',
                'description',
                'views',
                'status',
                'asset'
            ]);

            // Update file PDF, jika ada
            if ($request->hasFile('file')) {
                $pdfFile = $request->file('file');
                $pdfName = time() . '.' . $pdfFile->getClientOriginalExtension();
                $pdfPath = $pdfFile->storeAs('public/files', $pdfName);
                $productData['file'] = url('storage/files/' . $pdfName);
            }

            // Simpan file gambar, jika ada
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                $imageName = time() . '.' . $imageFile->getClientOriginalExtension();
                $imagePath = $imageFile->storeAs('public/images', $imageName);
                $videosData['image'] = url('storage/images/' . $imageName);
            }
            // Update produk
            $product->update($productData);
            // Update data categories
            if ($request->has('categories')) {
                $newCategories = $request->input('categories');

                // Hapus categories yang tidak ada dalam data baru
                $existingCategories = $this->videosCategory->where('videos_id', $product->id)->get();
                foreach ($existingCategories as $existingCategory) {
                    if (!in_array($existingCategory->category_id, $newCategories)) {
                        $existingCategory->delete();
                    }
                }

                // Tambahkan categories baru yang tidak ada dalam data lama
                foreach ($newCategories as $categoryId) {
                    if (!$existingCategories->contains('category_id', $categoryId)) {
                        $this->videosCategory->create([
                            'videos_id' => $product->id,
                            'category_id' => $categoryId
                        ]);
                    }
                }
            }

            // Commit transaksi
            DB::commit();

            return $product;
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            throw $e;
        }
    }

    public function cEdit($companyId, $slug)
    {
        $videos = $this->videos->newQuery()
            ->where('videos.slug', $slug)
            ->select(
                'videos.id',
                'videos.title',
                'videos.views',
                'videos.slug',
                'videos.description',
                'videos.asset',
                'videos.status'
            )->with(['videoCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        return $videos;
    }

    public function cDestroy($companyId, $slug)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Temukan produk berdasarkan companyId dan slug
            $videos = $this->videos->where('company_id', $companyId)->where('slug', $slug)->firstOrFail();

            // Hapus categories terkait
            $this->videosCategory->where('videos_id', $videos->id)->delete();

            // Hapus produk
            $videos->delete();

            // Commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'videos deleted successfully']);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            throw $e;
        }
    }


    public function clisting($companyId, $request)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Dapatkan product ID dari request
            $videoId = $request->input('videos_id');
            $status = $request->input('status'); // "draft" or "publish"

            // Temukan produk berdasarkan companyId dan videoId
            $video = $this->videos->where('company_id', $companyId)->where('id', $videoId)->firstOrFail();

            // Ubah status produk
            $video->status = $status;
            $video->save();

            // Commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'media status updated successfully']);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            throw $e;
        }
    }
}

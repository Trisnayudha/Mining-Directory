<?php

namespace App\Repositories\Eloquent;

use App\Models\News;
use App\Models\NewsCategory;
use App\Repositories\Contracts\NewsRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsRepository implements NewsRepositoryInterface
{
    protected $news;
    protected $newsCategory;

    public function __construct(News $news, NewsCategory $newsCategory)
    {
        $this->news = $news;
        $this->newsCategory = $newsCategory;
    }

    public function findHome()
    {
        return $this->news->join('company', 'company.id', 'news.company_id')->select('news.*', 'company.company_name')->take(5)->get();
    }
    public function findSearch($request)
    {
        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided
        $slug = $request->slug;

        $query = $this->news->newQuery();
        // Join with category and subcategory tables
        $query->join('company', 'company.id', '=', 'news.company_id')
            ->leftJoin('news_category_list', 'news_category_list.news_id', '=', 'news.id')
            ->leftJoin('md_category_company', 'news_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('news_sub_category_list', 'news_sub_category_list.news_id', '=', 'news.id')
            ->leftJoin('md_sub_category_company', 'news_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        if (!empty($slug)) {
            $query->where('company.slug', $slug);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('news.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('news.description', 'LIKE', '%' . $search . '%');
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
            'news.title',
            'news.slug',
            'news.image', // Get the asset from project_asset with asset_type png
            'company.company_name',
            'news.views',
            'news.date_news',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('news.id', 'news.title', 'news.slug', 'news.image', 'company.company_name', 'news.views', 'company.package', 'news.date_news')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }

    public function detail($slug)
    {
        $news = $this->news->newQuery()
            ->join('company', 'company.id', '=', 'news.company_id')
            ->where('news.slug', $slug)
            ->select(
                'news.id',
                'company.company_name',
                'company.package',
                'company.slug as company_slug',
                'company.image as company_image',
                'company.id as company_id',
                'news.views',
                'news.date_news',
                'news.title',
                'news.sub_title',
                'news.slug',
                'news.image',
                'news.description'
            )->with(['newsCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        // Mengambil nama kategori
        if ($news && $news->newsCategories->isNotEmpty()) {
            $news->category_name = $news->newsCategories->first()->mdCategory->name;
            unset($news->newsCategories); // Opsional: Hapus data newsCategories yang tidak perlu
        }

        // Menambahkan URL Share
        if ($news) {
            $url = 'https://mining-directory.vercel.app/news/detail/' . $slug;
            $news->share_links = [
                'web' => $url,
                'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url),
                'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($url),
                'instagram' => 'https://www.instagram.com/?url=' . urlencode($url), // Instagram tidak memiliki API khusus share, Anda bisa arahkan ke homepage
            ];
        }

        return $news;
    }


    public function moreList($request)
    {
        $company_id = $request->company_id;
        return $this->news->where('news.id', '=', $company_id)
            ->select('id', 'title', 'slug', 'image')->orderby('id', 'desc')->take(5)->get();
    }

    public function cIndex($companyId)
    {
        return  $this->news->where('company_id', $companyId)
            ->select('id', 'title', 'slug', 'views', 'image', 'status')->orderby('id', 'desc')->get();
    }

    public function cStore($companyId, $request)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Simpan data proyek
            $newsData = $request->only([
                'title',
                'sub_title',
                'description',
                'date_news',
                'image',
                'status',
            ]);

            // Buat slug berdasarkan title
            $newsData['slug'] = Str::slug($request->input('title') . '-' . time());
            $newsData['company_id'] = $companyId;

            // Simpan file gambar, jika ada
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                $imageName = time() . '.' . $imageFile->getClientOriginalExtension();
                $imagePath = $imageFile->storeAs('public/images', $imageName);
                $newsData['image'] = url('storage/images/' . $imageName);
            }

            $news = $this->news->create($newsData);

            // Simpan data categories
            if ($request->has('categories')) {
                foreach ($request->categories as $categoryId) {
                    $this->newsCategory->create([
                        'news_id' => $news->id,
                        'category_id' => $categoryId
                    ]);
                }
            }

            // Commit transaksi
            DB::commit();

            return $news;
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
            $product = $this->news->findOrFail($productId);

            // Update data produk
            $productData = $request->only([
                'title',
                'sub_title',
                'description',
                'status',
                'date_news'
            ]);
            // Simpan file gambar, jika ada
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                $imageName = time() . '.' . $imageFile->getClientOriginalExtension();
                $imagePath = $imageFile->storeAs('public/images', $imageName);
                $newsData['image'] = url('storage/images/' . $imageName);
            }
            // Update produk
            $product->update($productData);
            // Update data categories
            if ($request->has('categories')) {
                $newCategories = $request->input('categories');

                // Hapus categories yang tidak ada dalam data baru
                $existingCategories = $this->newsCategory->where('news_id', $product->id)->get();
                foreach ($existingCategories as $existingCategory) {
                    if (!in_array($existingCategory->category_id, $newCategories)) {
                        $existingCategory->delete();
                    }
                }

                // Tambahkan categories baru yang tidak ada dalam data lama
                foreach ($newCategories as $categoryId) {
                    if (!$existingCategories->contains('category_id', $categoryId)) {
                        $this->newsCategory->create([
                            'news_id' => $product->id,
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
        $news = $this->news->newQuery()
            ->where('news.slug', $slug)
            ->select(
                'news.id',
                'news.title',
                'news.sub_title',
                'news.views',
                'news.slug',
                'news.description',
                'news.image',
                'news.date_news'
            )->with(['newsCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        return $news;
    }

    public function cDestroy($companyId, $slug)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Temukan produk berdasarkan companyId dan slug
            $news = $this->news->where('company_id', $companyId)->where('slug', $slug)->firstOrFail();

            // Hapus categories terkait
            $this->newsCategory->where('news_id', $news->id)->delete();

            // Hapus produk
            $news->delete();

            // Commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'news deleted successfully']);
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
            $newsId = $request->input('news_id');
            $status = $request->input('status'); // "draft" or "publish"

            // Temukan produk berdasarkan companyId dan newsId
            $news = $this->news->where('company_id', $companyId)->where('id', $newsId)->firstOrFail();

            // Ubah status produk
            $news->status = $status;
            $news->save();

            // Commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'news status updated successfully']);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            throw $e;
        }
    }
}

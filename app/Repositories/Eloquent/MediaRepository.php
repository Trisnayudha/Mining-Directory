<?php

namespace App\Repositories\Eloquent;

use App\Models\MediaCategory;
use App\Models\MediaResource;
use App\Repositories\Contracts\MediaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaRepository implements MediaRepositoryInterface
{
    protected $media;
    protected $mediaCategory;
    public function __construct(MediaResource $media, MediaCategory $mediaCategory)
    {
        $this->media = $media;
        $this->mediaCategory = $mediaCategory;
    }

    public function findSearch($request)
    {

        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided
        $query = $this->media->newQuery();
        $slug = $request->slug;
        // Join with category and subcategory tables
        $query->join('company', 'company.id', '=', 'media_resource.company_id')
            ->leftJoin('media_resource_category_list', 'media_resource_category_list.media_resource_id', '=', 'media_resource.id')
            ->leftJoin('md_category_company', 'media_resource_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('media_resource_sub_category_list', 'media_resource_sub_category_list.media_resource_id', '=', 'media_resource.id')
            ->leftJoin('md_sub_category_company', 'media_resource_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        if (!empty($slug)) {
            $query->where('company.slug', $slug);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('media_resource.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('media_resource.description', 'LIKE', '%' . $search . '%');
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
            'media_resource.title',
            'media_resource.slug',
            'media_resource.image', // Get the asset from media_resource_asset with asset_type png
            'company.company_name',
            'media_resource.views',
            'media_resource.download',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('media_resource.id', 'media_resource.title', 'media_resource.slug', 'media_resource.image', 'company.company_name', 'media_resource.views', 'media_resource.download', 'company.package')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }

    public function detail($slug)
    {
        $media = $this->media->newQuery()
            ->join('company', 'company.id', 'media_resource.company_id')
            ->where('media_resource.slug', $slug)
            ->select(
                'media_resource.id',
                'company.company_name',
                'company.package',
                'company.slug as company_slug',
                'media_resource.title',
                'media_resource.views',
                'media_resource.download',
                'media_resource.description',
                'media_resource.file',
                'media_resource.image',
            )->with(['mediaCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        // Mengambil nama kategori
        if ($media && $media->mediaCategories->isNotEmpty()) {
            $media->category_name = $media->mediaCategories->first()->mdCategory->name;
            unset($media->mediaCategories); // Opsional: Hapus data mediaCategories yang tidak perlu
        }
        return $media;
    }

    public function cIndex($companyId)
    {
        return  $this->media->where('company_id', $companyId)
            ->with(['mediaCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])->select('id', 'title', 'slug', 'views', 'download', 'image', 'status')->orderby('id', 'desc')->get();
    }

    public function cStore($companyId, $request)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Simpan data proyek
            $mediaData = $request->only([
                'title',
                'description',
                'location',
                'status'
            ]);

            // Buat slug berdasarkan title
            $mediaData['slug'] = Str::slug($request->input('title') . '-' . time());
            $mediaData['company_id'] = $companyId;

            // Simpan file PDF, jika ada
            if ($request->hasFile('file')) {
                $pdfFile = $request->file('file');
                $pdfName = time() . '.' . $pdfFile->getClientOriginalExtension();
                $pdfPath = $pdfFile->storeAs('public/files', $pdfName);
                $mediaData['file'] = url('storage/files/' . $pdfName);
            }

            // Simpan file gambar, jika ada
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                $imageName = time() . '.' . $imageFile->getClientOriginalExtension();
                $imagePath = $imageFile->storeAs('public/images', $imageName);
                $mediaData['image'] = url('storage/images/' . $imageName);
            }

            $media = $this->media->create($mediaData);

            // Simpan data categories
            if ($request->has('categories')) {
                foreach ($request->categories as $categoryId) {
                    $this->mediaCategory->create([
                        'media_resource_id' => $media->id,
                        'category_id' => $categoryId
                    ]);
                }
            }

            // Commit transaksi
            DB::commit();

            return $media;
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
            $product = $this->media->findOrFail($productId);

            // Update data produk
            $productData = $request->only([
                'title',
                'description',
                'views',
                'download',
                'status'
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
                $mediaData['image'] = url('storage/images/' . $imageName);
            }
            // Update produk
            $product->update($productData);
            // Update data categories
            if ($request->has('categories')) {
                $newCategories = $request->input('categories');

                // Hapus categories yang tidak ada dalam data baru
                $existingCategories = $this->mediaCategory->where('media_resource_id', $product->id)->get();
                foreach ($existingCategories as $existingCategory) {
                    if (!in_array($existingCategory->category_id, $newCategories)) {
                        $existingCategory->delete();
                    }
                }

                // Tambahkan categories baru yang tidak ada dalam data lama
                foreach ($newCategories as $categoryId) {
                    if (!$existingCategories->contains('category_id', $categoryId)) {
                        $this->mediaCategory->create([
                            'media_resource_id' => $product->id,
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
        $media = $this->media->newQuery()
            ->where('media_resource.slug', $slug)
            ->select(
                'media_resource.id',
                'media_resource.title',
                'media_resource.views',
                'media_resource.download',
                'media_resource.slug',
                'media_resource.description',
                'media_resource.file',
                'media_resource.description',
                'media_resource.image',
            )->with(['mediaCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        return $media;
    }

    public function cDestroy($companyId, $slug)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Temukan produk berdasarkan companyId dan slug
            $media = $this->media->where('company_id', $companyId)->where('slug', $slug)->firstOrFail();

            // Hapus categories terkait
            $this->mediaCategory->where('media_resource_id', $media->id)->delete();

            // Hapus produk
            $media->delete();

            // Commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'media deleted successfully']);
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
            $mediaId = $request->input('media_id');
            $status = $request->input('status'); // "draft" or "publish"

            // Temukan produk berdasarkan companyId dan mediaId
            $media = $this->media->where('company_id', $companyId)->where('id', $mediaId)->firstOrFail();

            // Ubah status produk
            $media->status = $status;
            $media->save();

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

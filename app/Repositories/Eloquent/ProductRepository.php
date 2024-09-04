<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\ProductAsset;
use App\Models\ProductCategory;
use App\Models\ProductFavorite;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ProductRepository implements ProductRepositoryInterface
{
    protected $product;
    protected $productAsset;
    protected $productCategory;
    protected $productFavorite;

    public function __construct(Product $product, ProductAsset $productAsset, ProductCategory $productCategory, ProductFavorite $productFavorite)
    {
        $this->product = $product;
        $this->productAsset = $productAsset;
        $this->productCategory = $productCategory;
        $this->productFavorite = $productFavorite;
    }

    public function findHome()
    {
        return $this->product
            ->join('company', 'company.id', '=', 'products.company_id')
            ->leftJoin('products_asset', function ($join) {
                $join->on('products.id', '=', 'products_asset.product_id')
                    ->where('products_asset.asset_type', '=', 'png');
            })
            ->select('products.*', 'company.company_name')
            ->selectSub(function ($query) {
                $query->from('products_asset')
                    ->select('asset')
                    ->whereColumn('products.id', 'products_asset.product_id')
                    ->where('asset_type', 'png')
                    ->limit(1);
            }, 'asset')
            ->take(4)
            ->get();
    }

    public function findSearch($request)
    {
        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided
        $slug = $request->slug;

        $query = $this->product->newQuery();
        // Join with category and subcategory tables
        $query->join('company', 'company.id', '=', 'products.company_id')
            ->leftJoin('products_asset', function ($join) {
                $join->on('products_asset.product_id', '=', 'products.id')
                    ->where('products_asset.asset_type', '=', 'png');
            })
            ->leftJoin('products_category_list', 'products_category_list.product_id', '=', 'products.id')
            ->leftJoin('md_category_company', 'products_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('products_sub_category_list', 'products_sub_category_list.product_id', '=', 'products.id')
            ->leftJoin('md_sub_category_company', 'products_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        if (!empty($slug)) {
            $query->where('company.slug', $slug);
        }

        // Search for title and description
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('products.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('products.description', 'LIKE', '%' . $search . '%');
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
            'products.title',
            'products.slug',
            'products_asset.asset as image', // Get the asset from products_asset with asset_type png
            'company.company_name',
            'products.views',
            'products.description',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('products.id', 'products.title', 'products.slug', 'products_asset.asset', 'company.company_name', 'products.views', 'company.package', 'products.description')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }


    public function detail($slug, $id)
    {
        $product = $this->product->newQuery()
            ->join('company', 'company.id', '=', 'products.company_id')
            ->where('products.slug', '=', $slug)
            ->select(
                'products.id',
                'company.company_name',
                'company.slug as company_slug',
                'company.id as company_id',
                'company.package',
                'company.image as company_image',
                'products.title',
                'products.slug',
                'products.views',
                'products.download',
                'products.description',
            )
            ->with(['products_asset' => function ($query) {
                $query->select('product_id', 'asset'); // Asumsi ada 'product_id' di 'products_asset'
            }, 'productCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        if ($product && $product->products_asset) {
            $product->products_asset = $product->products_asset->pluck('asset');
        }

        // Mengambil nama kategori
        if ($product && $product->productCategories->isNotEmpty()) {
            $product->category_name = $product->productCategories->first()->mdCategory->name;
            unset($product->productCategories); // Opsional: Hapus data productCategories yang tidak perlu
        }

        // Menambahkan URL Share
        if ($product) {
            $url = 'https://mining-directory.vercel.app/product/detail/' . $slug;
            $product->share_links = [
                'web' => $url,
                'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url),
                'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($url),
                'instagram' => 'https://www.instagram.com/?url=' . urlencode($url), // Instagram tidak memiliki API khusus share, Anda bisa arahkan ke homepage
            ];
        }
        $findFavorite = null;
        if ($id) {
            $findFavorite = $this->productFavorite->where('users_id', $id)->where('product_id', $product->id)->first();
        }
        $product->isFavorite = $findFavorite ? 1 : 0;

        return $product;
    }


    public function moreList($request)
    {
        $company_id = $request->company_id;
        $expect_id = $request->expect_id;
        $products = $this->product->where('products.company_id', '=', $company_id)
            ->where('products.id', '!=', $expect_id)
            ->join('company', 'company.id', '=', 'products.company_id')
            ->select(
                'products.id',
                'products.title',
                'company.company_name',
                'products.slug',
                DB::raw('LEFT(products.description, 100) as description_short')
            )
            ->addSelect([
                'products_asset' => DB::table('products_asset')
                    ->select('asset')
                    ->whereColumn('products_asset.product_id', 'products.id')
                    ->where('asset_type', 'image')
                    ->limit(1)
            ])
            ->with(['productCategories.mdCategory' => function ($query) {
                $query->select('id', 'name')
                    ->limit(1); // Mengambil hanya satu kategori
            }])
            ->orderby('id', 'desc')
            ->get();

        // Memastikan deskripsi diakhiri dengan "..." jika lebih dari 100 karakter
        $products->each(function ($product) {
            if (strlen($product->description_short) >= 100) {
                $product->description_short .= '...';
            }
        });

        return $products;
    }

    public function relatedList($request)
    {
        $expect_id = $request->expect_id;
        $products = $this->product
            ->where('products.id', '!=', $expect_id)
            ->join('company', 'company.id', '=', 'products.company_id')
            ->select(
                'products.id',
                'products.title',
                'company.company_name',
                'products.slug',
                DB::raw('LEFT(products.description, 100) as description_short')
            )
            ->addSelect([
                'products_asset' => DB::table('products_asset')
                    ->select('asset')
                    ->whereColumn('products_asset.product_id', 'products.id')
                    ->where('asset_type', 'image')
                    ->limit(1)
            ])
            ->with(['productCategories.mdCategory' => function ($query) {
                $query->select('id', 'name')
                    ->limit(1); // Mengambil hanya satu kategori
            }])
            ->orderby('id', 'desc')
            ->get();

        // Memastikan deskripsi diakhiri dengan "..." jika lebih dari 100 karakter
        $products->each(function ($product) {
            if (strlen($product->description_short) >= 100) {
                $product->description_short .= '...';
            }
        });

        return $products;
    }

    public function download($slug)
    {
        $product = $this->product->where('slug', $slug)->select('id', 'file', 'download')->first();
        $product->download = $product->download + 1;

        // Simpan perubahan ke database
        $product->update();
        return $product;
    }


    public function cIndex($companyId)
    {
        return $this->product->where('company_id', $companyId)
            ->with(['productCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->select('id', 'title', 'slug', 'views', 'download', 'status')
            ->addSelect([
                'products_asset' => DB::table('products_asset')
                    ->select('asset')
                    ->whereColumn('products_asset.product_id', 'products.id')
                    ->where('asset_type', 'image')
                    ->limit(1)
            ])
            ->orderby('id', 'desc')
            ->get();
    }


    public function cStore($companyId, $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'views' => 'nullable|integer',
            'download' => 'nullable|integer',
            'status' => 'required|string',
            'file' => 'nullable|mimes:pdf|max:20480', // Maksimal 20MB untuk file PDF
            'assets.*' => 'nullable|mimes:jpeg,png,jpg,mp4|max:20480', // Maksimal 20MB untuk setiap asset (gambar/video)
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Simpan data produk
            $productData = $request->only([
                'title',
                'description',
                'views',
                'download',
                'status'
            ]);

            // Buat slug berdasarkan title
            $productData['slug'] = Str::slug($request->input('title') . '-' . time());
            $productData['company_id'] = $companyId;

            // Simpan file PDF, jika ada
            if ($request->hasFile('file')) {
                $pdfFile = $request->file('file');
                $pdfName = time() . '.' . $pdfFile->getClientOriginalExtension();
                $pdfPath = $pdfFile->storeAs('public/files', $pdfName);
                $productData['file'] = url('storage/files/' . $pdfName);
            }

            $product = $this->product->create($productData);

            // Simpan data assets
            if ($request->hasFile('assets')) {
                $this->saveProductAssets($product->id, $request->file('assets'));
            }

            if ($request->video_asset) {
                $this->saveVideoAssets($product->id, $request->video_asset);
            }

            // Simpan data categories
            if ($request->has('categories')) {
                foreach ($request->categories as $categoryId) {
                    $this->productCategory->create([
                        'product_id' => $product->id,
                        'category_id' => $categoryId
                    ]);
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


    public function cUpdate($productId, $request)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Temukan produk
            $product = $this->product->findOrFail($productId);
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

            // Update produk
            $product->update($productData);
            // dd($product);

            // Update data assets
            if ($request->hasFile('assets')) {
                $newAssets = $request->file('assets');

                // Hapus assets yang tidak ada dalam data baru
                $existingAssets = $this->productAsset->where('product_id', $product->id)->get();
                foreach ($existingAssets as $existingAsset) {
                    if (!in_array($existingAsset->asset, array_map(function ($file) {
                        return url('storage/assets/' . time() . '.' . $file->getClientOriginalExtension());
                    }, $newAssets)));
                }

                // Tambahkan assets baru yang tidak ada dalam data lama
                $this->saveProductAssets($product->id, $newAssets);
            }

            if ($request->video_asset) {
                $this->saveVideoAssets($product->id, $request->video_asset);
            }

            if ($request->has('remove_asset')) {
                $removeAsset = $request->input('remove_asset');
                foreach ($removeAsset as $key) {
                    $remove = $this->productAsset->where('id', $key)->first();
                    if ($remove) {
                        $remove->delete();
                    }
                }
            }

            // Update data categories
            if ($request->has('categories')) {
                $newCategories = $request->input('categories');

                // Hapus semua kategori yang ada untuk produk ini
                $this->productCategory->where('product_id', $product->id)->delete();

                // Tambahkan kategori baru
                if (is_array($newCategories)) {
                    foreach ($newCategories as $categoryId) {
                        $this->productCategory->create([
                            'product_id' => $product->id,
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
        $product = $this->product->newQuery()
            ->join('company', 'company.id', '=', 'products.company_id')
            ->where('products.slug', '=', $slug)
            ->select(
                'products.id',
                'products.title',
                'products.views',
                'products.download',
                'products.description',
                'products.file'
            )
            ->with(['products_asset' => function ($query) {
                $query->select('id', 'product_id', 'asset', 'asset_type');
            }, 'productCategories.mdCategory' => function ($query) {
                $query->select('id', 'name');
            }])
            ->first();

        if ($product) {
            // Pastikan products_asset tidak null
            if ($product->products_asset) {
                // Pisahkan assets berdasarkan tipe di dalam repository
                $imageAssets = $product->products_asset->where('asset_type', 'image')->values();
                $videoAssets = $product->products_asset->where('asset_type', 'video')->values();

                // Tambahkan assets yang sudah dipisahkan ke dalam array
                $product->image_assets = $imageAssets;
                $product->video_assets = $videoAssets;

                // Hapus collection asli untuk menghindari redundansi
                unset($product->products_asset);
            } else {
                // Jika products_asset null, inisialisasi sebagai koleksi kosong
                $product->image_assets = collect();
                $product->video_assets = collect();
            }
        } else {
            // Jika $product null, kembalikan respon atau handle error sesuai kebutuhan
            return response()->json(['error' => 'Product not found'], 404);
        }

        return $product;
    }



    public function cDestroy($companyId, $slug)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Temukan produk berdasarkan companyId dan slug
            $product = $this->product->where('company_id', $companyId)->where('slug', $slug)->firstOrFail();

            // Hapus assets terkait
            $this->productAsset->where('product_id', $product->id)->delete();

            // Hapus categories terkait
            $this->productCategory->where('product_id', $product->id)->delete();

            // Hapus produk
            $product->delete();

            // Commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
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
            $productId = $request->input('product_id');
            $status = $request->input('status'); // "draft" or "publish"

            // Temukan produk berdasarkan companyId dan productId
            $product = $this->product->where('company_id', $companyId)->where('id', $productId)->firstOrFail();

            // Ubah status produk
            $product->status = $status;
            $product->save();

            // Commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Product status updated successfully']);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            throw $e;
        }
    }

    private function saveProductAssets($productId, $files)
    {
        foreach ($files as $file) {
            // Menghasilkan nama file unik
            $imageName = uniqid() . '.' . $file->extension();
            $dbPath = 'storage/assets/' . $imageName;

            // Menyimpan file
            $file->storeAs('public/assets', $imageName);

            // Menentukan tipe aset berdasarkan mime type
            $mimeType = $file->getMimeType();
            $assetType = 'unknown';
            if (strstr($mimeType, "video/")) {
                $assetType = 'video';
            } elseif (strstr($mimeType, "image/")) {
                $assetType = 'image';
            }

            // Simpan path file ke database
            $this->productAsset->create([
                'product_id' => $productId,
                'asset' => url($dbPath),
                'asset_type' => $assetType
            ]);
        }
    }

    private function saveVideoAssets($productId, $files)
    {
        foreach ($files as $file) {
            $assetType = 'video';
            // Simpan path file ke database
            $this->productAsset->create([
                'product_id' => $productId,
                'asset' => $file,
                'asset_type' => $assetType
            ]);
        }
    }
}

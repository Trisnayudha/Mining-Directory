<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectRepository implements ProjectRepositoryInterface
{
    protected $project;
    protected $projectCategory;

    public function __construct(Project $project, ProjectCategory $projectCategory)
    {
        $this->project = $project;
        $this->projectCategory = $projectCategory;
    }

    public function findSearch($request)
    {
        $search = $request->search;
        $category_name = $request->category_id; // Assume this is the name of the category
        $sub_category_name = $request->sub_category_id; // Assume this is the name of the sub-category
        $paginate = $request->paginate ?? 12; // Default to 12 if not provided
        $slug = $request->slug;

        $query = $this->project->newQuery();
        // Join with category and subcategory tables
        $query->join('company', 'company.id', '=', 'projects.company_id')
            ->leftJoin('project_category_list', 'project_category_list.project_id', '=', 'projects.id')
            ->leftJoin('md_category_company', 'project_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('project_sub_category_list', 'project_sub_category_list.project_id', '=', 'projects.id')
            ->leftJoin('md_sub_category_company', 'project_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id');

        if (!empty($slug)) {
            $query->where('company.slug', $slug);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('projects.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('projects.description', 'LIKE', '%' . $search . '%');
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
            'projects.title',
            'projects.slug',
            'projects.image', // Get the asset from project_asset with asset_type png
            'company.company_name',
            'projects.views',
            'projects.download',
            DB::raw('MIN(md_category_company.name) as category') // Get one category name
        ])
            ->groupBy('projects.id', 'projects.title', 'projects.slug', 'projects.image', 'company.company_name', 'projects.views', 'company.package', 'projects.download')
            ->orderByRaw("FIELD(company.package, 'platinum', 'gold', 'silver')")
            ->paginate($paginate);

        return $results;
    }

    public function detail($slug)
    {
        $project = $this->project->newQuery()
            ->join('company', 'company.id', '=', 'projects.company_id')
            ->where('projects.slug', '=', $slug)
            ->select(
                'projects.id',
                'company.id as company_id',
                'company.company_name',
                'company.package',
                'company.slug as company_slug',
                'company.image as company_image',
                'projects.title',
                'projects.views',
                'projects.download',
                'projects.slug',
                'projects.description',
                'projects.file',
                'projects.image'
            )->with(['projectCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        // Mengambil nama kategori
        if ($project && $project->projectCategories->isNotEmpty()) {
            $project->category_name = $project->projectCategories->first()->mdCategory->name;
            unset($project->projectCategories); // Opsional: Hapus data projectCategories yang tidak perlu
        }

        // Menambahkan URL Share
        if ($project) {
            $url = 'https://mining-directory.vercel.app/project/detail/' . $slug;
            $project->share_links = [
                'web' => $url,
                'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url),
                'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($url),
                'instagram' => 'https://www.instagram.com/?url=' . urlencode($url), // Instagram tidak memiliki API khusus share, Anda bisa arahkan ke homepage
            ];
        }

        return $project;
    }


    public function moreList($request)
    {
        $company_id = $request->company_id;
        $expect_id = $request->expect_id;

        return $this->project
            ->join('company', 'company.id', '=', 'projects.company_id')
            ->leftJoin('project_category_list', 'project_category_list.project_id', '=', 'projects.id')
            ->leftJoin('md_category_company', 'project_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('project_sub_category_list', 'project_sub_category_list.project_id', '=', 'projects.id')
            ->leftJoin('md_sub_category_company', 'project_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id')
            ->where('projects.company_id', '=', $company_id)
            ->where('projects.id', '!=', $expect_id)
            ->select(
                'projects.id',
                'company.company_name',
                DB::raw('MIN(md_category_company.name) as category'),
                'projects.location',
                'projects.title',
                'projects.slug',
                'projects.description',
                'projects.image'
            )
            ->groupBy(
                'projects.id',
                'company.company_name',
                'projects.location',
                'projects.title',
                'projects.slug',
                'projects.description',
                'projects.image'
            )
            ->orderBy('projects.id', 'desc')
            ->take(4)
            ->get();
    }


    public function relatedList($request)
    {
        $expect_ids = $request->expect_id; // id in array

        return $this->project
            ->join('company', 'company.id', '=', 'projects.company_id')
            ->leftJoin('project_category_list', 'project_category_list.project_id', '=', 'projects.id')
            ->leftJoin('md_category_company', 'project_category_list.category_id', '=', 'md_category_company.id')
            ->leftJoin('project_sub_category_list', 'project_sub_category_list.project_id', '=', 'projects.id')
            ->leftJoin('md_sub_category_company', 'project_sub_category_list.sub_category_id', '=', 'md_sub_category_company.id')
            ->whereNotIn('projects.id', $expect_ids)
            ->select(
                'projects.id',
                'company.company_name',
                DB::raw('MIN(md_category_company.name) as category'), // Mengambil satu nama kategori
                'projects.location',
                'projects.title',
                'projects.slug',
                'projects.description',
                'projects.image'
            )
            ->groupBy(
                'projects.id',
                'company.company_name',
                'projects.location',
                'projects.title',
                'projects.slug',
                'projects.description',
                'projects.image'
            )
            ->orderBy('projects.id', 'desc')
            ->take(4)
            ->get();
    }


    public function download($slug)
    {
        $project = $this->project->where('slug', $slug)->select('id', 'file', 'download')->first();
        $project->download = $project->download + 1;

        // Simpan perubahan ke database
        $project->update();
        return $project;
    }

    public function cIndex($companyId)
    {
        return  $this->project->where('company_id', $companyId)
            ->with(['projectCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])->select('id', 'title', 'slug', 'views', 'download', 'image', 'status')->orderby('id', 'desc')->get();
    }

    public function cStore($companyId, $request)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Simpan data proyek
            $projectData = $request->only([
                'title',
                'description',
                'location',
                'status'
            ]);

            // Buat slug berdasarkan title
            $projectData['slug'] = Str::slug($request->input('title') . '-' . time());
            $projectData['company_id'] = $companyId;

            // Simpan file PDF, jika ada
            if ($request->hasFile('file')) {
                $pdfFile = $request->file('file');
                $pdfName = time() . '.' . $pdfFile->getClientOriginalExtension();
                $pdfPath = $pdfFile->storeAs('public/files', $pdfName);
                $projectData['file'] = url('storage/files/' . $pdfName);
            }

            // Simpan file gambar, jika ada
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                $imageName = time() . '.' . $imageFile->getClientOriginalExtension();
                $imagePath = $imageFile->storeAs('public/images', $imageName);
                $projectData['image'] = url('storage/images/' . $imageName);
            }

            $project = $this->project->create($projectData);

            // Simpan data categories
            if ($request->has('categories')) {
                foreach ($request->categories as $categoryId) {
                    $this->projectCategory->create([
                        'project_id' => $project->id,
                        'category_id' => $categoryId
                    ]);
                }
            }

            // Commit transaksi
            DB::commit();

            return $project;
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
            $product = $this->project->findOrFail($productId);

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
                $projectData['image'] = url('storage/images/' . $imageName);
            }
            // Update produk
            $product->update($productData);
            // Update data categories
            if ($request->has('categories')) {
                $newCategories = $request->input('categories');

                // Hapus categories yang tidak ada dalam data baru
                $existingCategories = $this->projectCategory->where('project_id', $product->id)->get();
                foreach ($existingCategories as $existingCategory) {
                    if (!in_array($existingCategory->category_id, $newCategories)) {
                        $existingCategory->delete();
                    }
                }

                // Tambahkan categories baru yang tidak ada dalam data lama
                foreach ($newCategories as $categoryId) {
                    if (!$existingCategories->contains('category_id', $categoryId)) {
                        $this->projectCategory->create([
                            'project_id' => $product->id,
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
        $project = $this->project->newQuery()
            ->where('projects.slug', $slug)
            ->select(
                'projects.id',
                'projects.title',
                'projects.views',
                'projects.download',
                'projects.slug',
                'projects.description',
                'projects.file',
                'projects.description',
                'projects.image',
            )->with(['projectCategories.mdCategory' => function ($query) {
                $query->select('id', 'name'); // Sesuaikan field sesuai dengan kebutuhan
            }])
            ->first();

        return $project;
    }

    public function cDestroy($companyId, $slug)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Temukan produk berdasarkan companyId dan slug
            $project = $this->project->where('company_id', $companyId)->where('slug', $slug)->firstOrFail();

            // Hapus categories terkait
            $this->projectCategory->where('project_id', $project->id)->delete();

            // Hapus produk
            $project->delete();

            // Commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'project deleted successfully']);
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
            $projectId = $request->input('project_id');
            $status = $request->input('status'); // "draft" or "publish"

            // Temukan produk berdasarkan companyId dan projectId
            $project = $this->project->where('company_id', $companyId)->where('id', $projectId)->firstOrFail();

            // Ubah status produk
            $project->status = $status;
            $project->save();

            // Commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'project status updated successfully']);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            throw $e;
        }
    }
}

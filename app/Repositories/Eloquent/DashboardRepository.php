<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Models\CompanyBusinessCard;
use App\Models\CompanyInquiry;
use App\Models\CompanyLog;
use App\Models\Example;
use App\Models\MediaResource;
use App\Models\MediaResourceLog;
use App\Models\News;
use App\Models\NewsLog;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Project;
use App\Models\ProjectLog;
use App\Models\Videos;
use App\Models\VideosLog;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    protected $company;
    protected $companyInquiry;
    protected $companyLog;

    public function __construct(
        Company $company,
        CompanyInquiry $companyInquiry,
        CompanyLog $companyLog
    ) {
        $this->company = $company;
        $this->companyInquiry = $companyInquiry;
        $this->companyLog = $companyLog;
    }

    public function card($id, $request)
    {
        $countInquiry = $this->countInquiry($id, $request);
        $countVisitor = $this->countVisitor($id, $request);
        $countAsset = $this->countAsset($id);

        $array = [
            'asset' => $countAsset,
            'visitor' => $countVisitor,
            'inquiry' => $countInquiry
        ];

        return $array;
    }

    public function listVisitor($id, $request)
    {
        $limit = $request->limit;
        return CompanyLog::join('users', 'users.id', 'company_log.users_id')->select('name', 'users.company_name', 'job_title', 'email')->where('company_id', $id)->orderby('company_log.id', 'desc')->paginate($limit);
    }

    public function listInquiry($id, $request)
    {
        $limit = $request->limit ?? 5;
        $inquiries = CompanyInquiry::leftJoin('users', 'users.id', 'company_inquiry.users_id')
            ->select('company_inquiry.*', 'users.name as users_name', 'users.email as users_email')
            ->orderby('company_inquiry.id', 'desc')
            ->paginate($limit);

        $inquiries->getCollection()->transform(function ($inquiry) {
            if ($inquiry->users_name !== null) {
                $inquiry->name = $inquiry->users_name;
            }
            if ($inquiry->users_email !== null) {
                $inquiry->email = $inquiry->users_email;
            }
            return $inquiry;
        });

        return $inquiries;
    }

    public function approveInquiry($request)
    {
        $action = $request->action;
        $inquiryId = $request->inquiry_id;
        $inquiry = CompanyInquiry::find($inquiryId);
        $inquiry->status = $action;
        $inquiry->save();
        return $inquiry;
    }


    public function listBusinessCard($id, $request)
    {
        $limit = $request->limit ?? 5;
        return CompanyBusinessCard::join('users', 'users.id', 'company_users_buisnesscard.users_id')
            ->select(
                'users.name as sender',
                'users.email',
                'users.company_name',
                'users.job_title',
                'users.prefix_phone',
                'users.phone'
            )
            ->where('company_id', $id)->paginate($limit);
    }

    public function visitAnalyst($companyId, $request)
    {
        $query = CompanyLog::where('company_id', $companyId);

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $currentWeek = Carbon::now()->weekOfMonth; // Get the current week of the month

        if ($request->input('filter') == 'year') {
            $query->whereYear('created_at', $currentYear);
            $logs = $query->get();

            // Group logs by month and count the visits
            $data = $logs->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            })->map(function ($row) {
                return $row->count();
            });

            // Ensure all months are present in the data
            $result = [];
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for ($i = 1; $i <= 12; $i++) {
                $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                $result[] = [
                    'month' => $months[$i - 1],
                    'visits' => $data->get($month, 0)
                ];
            }

            $formattedData = [
                'labels' => $months,
                'data' => [
                    [
                        'name' => 'Visits',
                        'type' => 'column',
                        'data' => array_column($result, 'visits'),
                        'color' => '#00ceff' // Example color, adjust as needed
                    ]
                ]
            ];
        } elseif ($request->input('filter') == 'month') {
            $query->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth);
            $logs = $query->get();

            // Group logs by week and count the visits
            $data = $logs->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->weekOfMonth;
            })->map(function ($row) {
                return $row->count();
            });

            // Ensure all weeks are present in the data
            $result = [];
            for ($i = 1; $i <= 4; $i++) {
                $result[] = [
                    'week' => 'Week ' . $i,
                    'visits' => $data->get($i, 0)
                ];
            }

            $formattedData = [
                'labels' => array_map(function ($item) {
                    return $item['week'];
                }, $result),
                'data' => [
                    [
                        'name' => 'Visits',
                        'type' => 'column',
                        'data' => array_column($result, 'visits'),
                        'color' => '#00ceff' // Example color, adjust as needed
                    ]
                ]
            ];
        } elseif ($request->input('filter') == 'week') {
            $query->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->where(function ($query) {
                    $currentWeekStart = Carbon::now()->startOfWeek();
                    $currentWeekEnd = Carbon::now()->endOfWeek();
                    $query->whereBetween('created_at', [$currentWeekStart, $currentWeekEnd]);
                });

            $logs = $query->get();

            // Group logs by day and count the visits
            $data = $logs->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('l');
            })->map(function ($row) {
                return $row->count();
            });

            // Ensure all days of the week are present in the data
            $result = [];
            $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($daysOfWeek as $day) {
                $result[] = [
                    'day' => $day,
                    'visits' => $data->get($day, 0)
                ];
            }

            $formattedData = [
                'labels' => $daysOfWeek,
                'data' => [
                    [
                        'name' => 'Visits',
                        'type' => 'column',
                        'data' => array_column($result, 'visits'),
                        'color' => '#00ceff' // Example color, adjust as needed
                    ]
                ]
            ];
        } else {
            $formattedData = [];
        }

        return $formattedData;
    }



    public function assetAnalyst($companyId, $request)
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $currentWeek = Carbon::now()->weekOfYear;

        // Initialize visit counts for each asset type
        $visitCounts = [
            'Project' => 0,
            'News' => 0,
            'Product' => 0,
            'Video' => 0,
            'Media Resource' => 0,
        ];

        if ($request->input('filter') == 'year') {
            $year = $request->input('year', $currentYear);

            $visitCounts['Project'] = ProjectLog::whereHas('project', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->count();

            $visitCounts['News'] = NewsLog::whereHas('news', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->count();

            $visitCounts['Product'] = ProductLog::whereHas('product', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->count();

            $visitCounts['Video'] = VideosLog::whereHas('video', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->count();

            $visitCounts['Media Resource'] = MediaResourceLog::whereHas('mediaResource', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->count();
        } elseif ($request->input('filter') == 'month') {
            $year = $request->input('year', $currentYear);
            $month = $request->input('month', $currentMonth);

            $visitCounts['Project'] = ProjectLog::whereHas('project', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();

            $visitCounts['News'] = NewsLog::whereHas('news', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();

            $visitCounts['Product'] = ProductLog::whereHas('product', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();

            $visitCounts['Video'] = VideosLog::whereHas('video', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();

            $visitCounts['Media Resource'] = MediaResourceLog::whereHas('mediaResource', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        } elseif ($request->input('filter') == 'week') {
            $year = $request->input('year', $currentYear);
            $month = $request->input('month', $currentMonth);
            $week = $request->input('week', $currentWeek);

            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            $visitCounts['Project'] = ProjectLog::whereHas('project', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

            $visitCounts['News'] = NewsLog::whereHas('news', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

            $visitCounts['Product'] = ProductLog::whereHas('product', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

            $visitCounts['Video'] = VideosLog::whereHas('video', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

            $visitCounts['Media Resource'] = MediaResourceLog::whereHas('mediaResource', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
        }

        // Generate colors array
        $colors = ["#92D3D3", "#60BEBE", "#2C6D6D", "#1B4242", "#124141"];

        // Filter out asset types with zero visits
        $filteredVisitCounts = array_filter($visitCounts, function ($count) {
            return $count > 0;
        });

        return [
            'series' => array_values($filteredVisitCounts),
            'labels' => array_keys($filteredVisitCounts),
            'colors' => array_slice($colors, 0, count($filteredVisitCounts))
        ];
    }




    private function countInquiry($id, $request)
    {
        return CompanyInquiry::where('company_id', $id)->count();
    }

    private function countVisitor($id, $request)
    {
        return CompanyLog::where('company_id', $id)->count();
    }

    private function countAsset($id)
    {
        $product = Product::where('company_id', $id)->count();
        $project = Project::where('company_id', $id)->count();
        $media = MediaResource::where('company_id', $id)->count();
        $news = News::where('company_id', $id)->count();
        $video = Videos::where('company_id', $id)->count();
        $total = $product + $project + $media + $news + $video;
        return $total;
    }
}

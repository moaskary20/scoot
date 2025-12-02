<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scooter;
use App\Models\User;
use App\Models\Trip;
use App\Models\WalletTransaction;
use App\Models\Penalty;
use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function overview(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $stats = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'scooters' => [
                'total' => Scooter::count(),
                'active' => Scooter::where('is_active', true)->count(),
                'available' => Scooter::where('status', 'available')->count(),
                'rented' => Scooter::where('status', 'rented')->count(),
                'charging' => Scooter::where('status', 'charging')->count(),
                'maintenance' => Scooter::where('status', 'maintenance')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'new_this_period' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'trips' => [
                'total' => Trip::whereBetween('start_time', [$dateFrom, $dateTo])->count(),
                'completed' => Trip::whereBetween('start_time', [$dateFrom, $dateTo])
                    ->where('status', 'completed')->count(),
                'cancelled' => Trip::whereBetween('start_time', [$dateFrom, $dateTo])
                    ->where('status', 'cancelled')->count(),
                'total_duration' => Trip::whereBetween('start_time', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->sum('duration_minutes'),
                'avg_duration' => Trip::whereBetween('start_time', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->avg('duration_minutes'),
            ],
            'revenue' => [
                'total' => Trip::whereBetween('start_time', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->sum('cost'),
                'from_trips' => Trip::whereBetween('start_time', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->sum('base_cost'),
                'from_penalties' => Penalty::whereBetween('paid_at', [$dateFrom, $dateTo])
                    ->where('status', 'paid')
                    ->sum('amount'),
                'wallet_top_ups' => WalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('type', 'top_up')
                    ->where('status', 'completed')
                    ->sum('amount'),
            ],
            'penalties' => [
                'total' => Penalty::whereBetween('applied_at', [$dateFrom, $dateTo])->count(),
                'paid' => Penalty::whereBetween('applied_at', [$dateFrom, $dateTo])
                    ->where('status', 'paid')->count(),
                'pending' => Penalty::whereBetween('applied_at', [$dateFrom, $dateTo])
                    ->where('status', 'pending')->count(),
                'total_amount' => Penalty::whereBetween('applied_at', [$dateFrom, $dateTo])
                    ->sum('amount'),
            ],
            'maintenance' => [
                'total' => MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])->count(),
                'completed' => MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])
                    ->where('status', 'completed')->count(),
                'in_progress' => MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])
                    ->where('status', 'in_progress')->count(),
                'total_cost' => MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->sum('actual_cost'),
            ],
        ];

        return view('admin.reports.overview', compact('stats'));
    }

    public function trips(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $trips = Trip::whereBetween('start_time', [$dateFrom, $dateTo])
            ->with(['user', 'scooter'])
            ->orderByDesc('start_time')
            ->paginate(50);

        // Daily statistics
        $dailyStats = Trip::whereBetween('start_time', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(start_time) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(cost) as revenue'),
                DB::raw('SUM(duration_minutes) as total_duration'),
                DB::raw('AVG(duration_minutes) as avg_duration')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Scooter statistics
        $scooterStats = Trip::whereBetween('trips.start_time', [$dateFrom, $dateTo])
            ->where('trips.status', 'completed')
            ->join('scooters', 'trips.scooter_id', '=', 'scooters.id')
            ->select(
                'scooters.code',
                DB::raw('COUNT(*) as trip_count'),
                DB::raw('SUM(trips.cost) as revenue'),
                DB::raw('SUM(trips.duration_minutes) as total_duration')
            )
            ->groupBy('scooters.id', 'scooters.code')
            ->orderByDesc('trip_count')
            ->limit(10)
            ->get();

        return view('admin.reports.trips', compact('trips', 'dailyStats', 'scooterStats', 'dateFrom', 'dateTo'));
    }

    public function revenue(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Daily revenue
        $dailyRevenue = Trip::whereBetween('start_time', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(start_time) as date'),
                DB::raw('SUM(cost) as revenue'),
                DB::raw('COUNT(*) as trip_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by type
        $revenueByType = [
            'trips' => Trip::whereBetween('start_time', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->sum('cost'),
            'penalties' => Penalty::whereBetween('paid_at', [$dateFrom, $dateTo])
                ->where('status', 'paid')
                ->sum('amount'),
            'wallet_top_ups' => WalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('type', 'top_up')
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        // Monthly comparison
        $currentMonth = Trip::whereBetween('start_time', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])
            ->where('status', 'completed')
            ->sum('cost');

        $lastMonth = Trip::whereBetween('start_time', [
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth()
        ])
            ->where('status', 'completed')
            ->sum('cost');

        return view('admin.reports.revenue', compact(
            'dailyRevenue',
            'revenueByType',
            'currentMonth',
            'lastMonth',
            'dateFrom',
            'dateTo'
        ));
    }

    public function users(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Top users by trips
        $topUsersByTrips = User::withCount(['trips' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('start_time', [$dateFrom, $dateTo]);
        }])
            ->withSum(['trips' => function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'completed');
            }], 'cost')
            ->orderByDesc('trips_count')
            ->limit(10)
            ->get();

        // Top users by spending
        $topUsersBySpending = User::withSum(['trips' => function ($query) use ($dateFrom, $dateTo) {
            $query->where('status', 'completed')
                ->whereBetween('start_time', [$dateFrom, $dateTo]);
        }], 'cost')
            ->orderByDesc('trips_sum_cost')
            ->limit(10)
            ->get();

        // User registration stats
        $userRegistrations = User::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.reports.users', compact(
            'topUsersByTrips',
            'topUsersBySpending',
            'userRegistrations',
            'dateFrom',
            'dateTo'
        ));
    }

    public function scooters(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Scooter performance
        $scooterPerformance = Scooter::withCount(['trips' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('start_time', [$dateFrom, $dateTo])
                ->where('status', 'completed');
        }])
            ->withSum(['trips' => function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'completed')
                    ->whereBetween('start_time', [$dateFrom, $dateTo]);
            }], 'cost')
            ->withSum(['trips' => function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'completed')
                    ->whereBetween('start_time', [$dateFrom, $dateTo]);
            }], 'duration_minutes')
            ->orderByDesc('trips_count')
            ->get();

        // Scooter status distribution
        $statusDistribution = Scooter::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Battery statistics
        $batteryStats = [
            'avg' => Scooter::where('is_active', true)->avg('battery_percentage'),
            'low' => Scooter::where('is_active', true)->where('battery_percentage', '<', 20)->count(),
            'medium' => Scooter::where('is_active', true)
                ->whereBetween('battery_percentage', [20, 80])
                ->count(),
            'high' => Scooter::where('is_active', true)->where('battery_percentage', '>', 80)->count(),
        ];

        return view('admin.reports.scooters', compact(
            'scooterPerformance',
            'statusDistribution',
            'batteryStats',
            'dateFrom',
            'dateTo'
        ));
    }

    public function maintenance(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $maintenanceRecords = MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])
            ->with(['scooter'])
            ->orderByDesc('reported_at')
            ->paginate(50);

        // Maintenance statistics
        $maintenanceStats = [
            'by_type' => MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])
                ->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get(),
            'by_status' => MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
            'by_priority' => MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])
                ->select('priority', DB::raw('COUNT(*) as count'))
                ->groupBy('priority')
                ->get(),
            'total_cost' => MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->sum('actual_cost'),
            'avg_cost' => MaintenanceRecord::whereBetween('reported_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->avg('actual_cost'),
        ];

        return view('admin.reports.maintenance', compact('maintenanceRecords', 'maintenanceStats', 'dateFrom', 'dateTo'));
    }

    public function wallet(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $transactions = WalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['user', 'trip'])
            ->orderByDesc('created_at')
            ->paginate(50);

        // Wallet statistics
        $walletStats = [
            'total_top_ups' => WalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('type', 'top_up')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_deductions' => WalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('transaction_type', 'debit')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_refunds' => WalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('type', 'refund')
                ->where('status', 'completed')
                ->sum('amount'),
            'by_type' => WalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->select('type', DB::raw('SUM(amount) as total'))
                ->groupBy('type')
                ->get(),
            'daily_flow' => WalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(CASE WHEN transaction_type = "credit" THEN amount ELSE 0 END) as credits'),
                    DB::raw('SUM(CASE WHEN transaction_type = "debit" THEN amount ELSE 0 END) as debits')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return view('admin.reports.wallet', compact('transactions', 'walletStats', 'dateFrom', 'dateTo'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Scooter;
use App\Models\ScooterLog;
use App\Models\User;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $stats = [
            'total_scooters' => Scooter::count(),
            'active_scooters' => Scooter::where('is_active', true)->where('status', 'available')->count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'trips_today' => Trip::whereDate('start_time', $today)->count(),
            'revenue_today' => Trip::whereDate('start_time', $today)
                ->where('status', 'completed')
                ->sum('cost'),
            'revenue_month' => Trip::where('start_time', '>=', $monthStart)
                ->where('status', 'completed')
                ->sum('cost'),
            'active_trips' => Trip::where('status', 'active')->count(),
            'charging_scooters' => Scooter::where('status', 'charging')->count(),
            'maintenance_scooters' => Scooter::where('status', 'maintenance')->count(),
            'zone_exit_alerts' => Trip::whereDate('start_time', $today)
                ->where('zone_exit_detected', true)
                ->count(),
            'critical_alerts' => ScooterLog::where('severity', 'critical')
                ->where('is_resolved', false)
                ->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}

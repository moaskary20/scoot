<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scooter;
use App\Models\ScooterLog;
use App\Repositories\ScooterLogRepository;
use Illuminate\Http\Request;

class ScooterLogController extends Controller
{
    public function __construct(
        private readonly ScooterLogRepository $repository,
    ) {
        //
    }

    public function index(Request $request)
    {
        $query = ScooterLog::query()->with(['scooter', 'trip', 'user']);

        // Filters
        if ($request->filled('scooter_id')) {
            $query->where('scooter_id', $request->scooter_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('is_resolved')) {
            $query->where('is_resolved', $request->is_resolved === '1');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.scooter-logs.index', compact('logs'));
    }

    public function show(ScooterLog $scooterLog)
    {
        $scooterLog->load(['scooter', 'trip', 'user']);

        return view('admin.scooter-logs.show', compact('scooterLog'));
    }

    public function markAsResolved(Request $request, ScooterLog $scooterLog)
    {
        $data = $request->validate([
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->repository->markAsResolved($scooterLog, $data['resolution_notes'] ?? null);

        return redirect()
            ->route('admin.scooter-logs.show', $scooterLog)
            ->with('status', __('Log marked as resolved.'));
    }

    public function getCriticalAlerts()
    {
        $criticalLogs = $this->repository->getCriticalLogs(10);

        return view('admin.scooter-logs.critical', compact('criticalLogs'));
    }
}

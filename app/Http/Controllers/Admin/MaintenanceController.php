<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRecord;
use App\Models\Scooter;
use App\Repositories\MaintenanceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MaintenanceController extends Controller
{
    public function __construct(
        private readonly MaintenanceRepository $repository,
    ) {
        //
    }

    public function index(Request $request)
    {
        $query = MaintenanceRecord::query()->with(['scooter', 'scooterLog']);

        // Filters
        if ($request->filled('scooter_id')) {
            $query->where('scooter_id', $request->scooter_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('reported_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('reported_at', '<=', $request->date_to);
        }

        $records = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.maintenance.index', compact('records'));
    }

    public function create(Request $request)
    {
        $scooterId = $request->get('scooter_id');
        $scooterLogId = $request->get('scooter_log_id');
        
        $scooter = $scooterId ? Scooter::find($scooterId) : null;
        $scooters = Scooter::where('status', '!=', 'maintenance')->orderBy('code')->get();

        return view('admin.maintenance.create', compact('scooters', 'scooter', 'scooterLogId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scooter_id' => ['required', 'exists:scooters,id'],
            'scooter_log_id' => ['nullable', 'exists:scooter_logs,id'],
            'type' => ['required', 'in:scheduled,repair,battery_replacement,firmware_update,inspection,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'fault_details' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'scheduled_at' => ['nullable', 'date'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $scooter = Scooter::findOrFail($data['scooter_id']);

        // Check if scooter is already in maintenance
        if ($scooter->status === 'maintenance') {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['scooter_id' => __('This scooter is already in maintenance.')]);
        }

        $this->repository->sendToMaintenance(
            $scooter,
            $data['title'],
            $data['type'],
            $data['description'] ?? null,
            $data['fault_details'] ?? null,
            $data['scooter_log_id'] ?? null,
            $data['priority'],
            $data['scheduled_at'] ? Carbon::parse($data['scheduled_at']) : null
        );

        return redirect()
            ->route('admin.maintenance.index')
            ->with('status', __('Scooter sent to maintenance successfully.'));
    }

    public function show(MaintenanceRecord $maintenance)
    {
        $maintenance->load(['scooter', 'scooterLog']);

        return view('admin.maintenance.show', compact('maintenance'));
    }

    public function edit(MaintenanceRecord $maintenance)
    {
        $maintenance->load(['scooter']);
        $scooters = Scooter::orderBy('code')->get();

        return view('admin.maintenance.edit', compact('maintenance', 'scooters'));
    }

    public function update(Request $request, MaintenanceRecord $maintenance)
    {
        $data = $request->validate([
            'type' => ['required', 'in:scheduled,repair,battery_replacement,firmware_update,inspection,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'fault_details' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'scheduled_at' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'technician_name' => ['nullable', 'string', 'max:255'],
            'technician_phone' => ['nullable', 'string', 'max:50'],
            'technician_email' => ['nullable', 'email', 'max:255'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'technician_notes' => ['nullable', 'string', 'max:2000'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
            'parts_replaced' => ['nullable', 'string', 'max:1000'],
            'quality_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'quality_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Parse dates
        if ($data['scheduled_at']) {
            $data['scheduled_at'] = Carbon::parse($data['scheduled_at']);
        }
        if ($data['started_at']) {
            $data['started_at'] = Carbon::parse($data['started_at']);
        }
        if ($data['completed_at']) {
            $data['completed_at'] = Carbon::parse($data['completed_at']);
        }

        $this->repository->update($maintenance, $data);

        // If status changed to completed, update scooter status
        if ($data['status'] === 'completed' && $maintenance->scooter->status === 'maintenance') {
            $maintenance->scooter->update([
                'status' => 'available',
                'last_maintenance_at' => Carbon::now(),
            ]);
        }

        return redirect()
            ->route('admin.maintenance.show', $maintenance)
            ->with('status', __('Maintenance record updated successfully.'));
    }

    public function destroy(MaintenanceRecord $maintenance)
    {
        // If maintenance is in progress, change scooter status back
        if ($maintenance->status === 'in_progress' && $maintenance->scooter->status === 'maintenance') {
            $maintenance->scooter->update(['status' => 'available']);
        }

        $this->repository->delete($maintenance);

        return redirect()
            ->route('admin.maintenance.index')
            ->with('status', __('Maintenance record deleted successfully.'));
    }

    public function start(Request $request, MaintenanceRecord $maintenance)
    {
        $data = $request->validate([
            'technician_name' => ['nullable', 'string', 'max:255'],
            'technician_phone' => ['nullable', 'string', 'max:50'],
            'technician_email' => ['nullable', 'email', 'max:255'],
        ]);

        $this->repository->startMaintenance($maintenance, $data);

        return redirect()
            ->route('admin.maintenance.show', $maintenance)
            ->with('status', __('Maintenance started successfully.'));
    }

    public function complete(Request $request, MaintenanceRecord $maintenance)
    {
        $data = $request->validate([
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
            'parts_replaced' => ['nullable', 'string', 'max:1000'],
            'quality_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $this->repository->completeMaintenance(
            $maintenance,
            $data['actual_cost'] ?? null,
            $data['resolution_notes'] ?? null,
            $data['parts_replaced'] ?? null,
            $data['quality_rating'] ?? null
        );

        return redirect()
            ->route('admin.maintenance.show', $maintenance)
            ->with('status', __('Maintenance completed successfully. Scooter is now available.'));
    }

    public function cancel(Request $request, MaintenanceRecord $maintenance)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->repository->cancelMaintenance($maintenance, $data['reason'] ?? null);

        return redirect()
            ->route('admin.maintenance.show', $maintenance)
            ->with('status', __('Maintenance cancelled successfully.'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeoZone;
use App\Repositories\GeoZoneRepository;
use Illuminate\Http\Request;

class GeoZoneController extends Controller
{
    public function __construct(
        private readonly GeoZoneRepository $repository,
    ) {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zones = $this->repository->paginate(20);

        return view('admin.geo-zones.index', compact('zones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.geo-zones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:allowed,forbidden,parking'],
            'color' => ['required', 'string', 'max:7'],
            'polygon' => ['required', 'string'], // JSON string from JS
            'center_latitude' => ['nullable', 'numeric'],
            'center_longitude' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['polygon'] = json_decode($data['polygon'], true);
        $data['is_active'] = $request->boolean('is_active', true);

        $this->repository->create($data);

        return redirect()
            ->route('admin.geo-zones.index')
            ->with('status', __('Geo zone created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(GeoZone $geoZone)
    {
        return view('admin.geo-zones.show', ['zone' => $geoZone]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GeoZone $geoZone)
    {
        return view('admin.geo-zones.edit', ['zone' => $geoZone]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GeoZone $geoZone)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:allowed,forbidden,parking'],
            'color' => ['required', 'string', 'max:7'],
            'polygon' => ['required', 'string'],
            'center_latitude' => ['nullable', 'numeric'],
            'center_longitude' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['polygon'] = json_decode($data['polygon'], true);
        $data['is_active'] = $request->boolean('is_active', $geoZone->is_active);

        $this->repository->update($geoZone, $data);

        return redirect()
            ->route('admin.geo-zones.index')
            ->with('status', __('Geo zone updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GeoZone $geoZone)
    {
        $this->repository->delete($geoZone);

        return redirect()
            ->route('admin.geo-zones.index')
            ->with('status', __('Geo zone deleted successfully.'));
    }
}

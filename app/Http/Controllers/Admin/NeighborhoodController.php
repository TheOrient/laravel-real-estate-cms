<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\District;
use App\Models\Neighborhood;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NeighborhoodController extends Controller
{
    /**
     * Display a listing of neighborhoods.
     */
    public function index(Request $request)
    {
        $query = Neighborhood::with(['district.city'])->withCount('listings');

        // Filter by city if provided
        if ($request->filled('city_id')) {
            $query->whereHas('district', function($q) use ($request) {
                $q->where('city_id', $request->city_id);
            });
        }

        // Filter by district if provided
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        $neighborhoods = $query->orderBy('name')->paginate(20);
        $cities = City::orderBy('name')->get();
        $districts = District::orderBy('name')->get();

        return view('admin.locations.neighborhoods.index', compact('neighborhoods', 'cities', 'districts'));
    }

    /**
     * Show the form for creating a new neighborhood.
     */
    public function create(Request $request)
    {
        $cities = City::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        $selectedDistrictId = $request->get('district_id');
        $selectedCityId = $request->get('city_id');

        return view('admin.locations.neighborhoods.create', compact('cities', 'districts', 'selectedDistrictId', 'selectedCityId'));
    }

    /**
     * Store a newly created neighborhood in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
            'is_active' => 'boolean'
        ]);

        // Check for unique name within the same district
        $exists = Neighborhood::where('district_id', $request->district_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'name' => 'Bu ilçede aynı isimde bir mahalle zaten mevcut.'
            ]);
        }

        Neighborhood::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'district_id' => $request->district_id,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.locations.neighborhoods.index')
            ->with('success', 'Mahalle başarıyla eklendi.');
    }

    /**
     * Display the specified neighborhood.
     */
    public function show(Neighborhood $neighborhood)
    {
        $neighborhood->load(['district.city', 'listings']);
        return view('admin.locations.neighborhoods.show', compact('neighborhood'));
    }

    /**
     * Show the form for editing the specified neighborhood.
     */
    public function edit(Neighborhood $neighborhood)
    {
        $cities = City::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        return view('admin.locations.neighborhoods.edit', compact('neighborhood', 'cities', 'districts'));
    }

    /**
     * Update the specified neighborhood in storage.
     */
    public function update(Request $request, Neighborhood $neighborhood)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
            'is_active' => 'boolean'
        ]);

        // Check for unique name within the same district (excluding current neighborhood)
        $exists = Neighborhood::where('district_id', $request->district_id)
            ->where('name', $request->name)
            ->where('id', '!=', $neighborhood->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'name' => 'Bu ilçede aynı isimde bir mahalle zaten mevcut.'
            ]);
        }

        $neighborhood->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'district_id' => $request->district_id,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.locations.neighborhoods.index')
            ->with('success', 'Mahalle başarıyla güncellendi.');
    }

    /**
     * Remove the specified neighborhood from storage.
     */
    public function destroy(Neighborhood $neighborhood)
    {
        // Check if neighborhood has listings
        if ($neighborhood->listings()->count() > 0) {
            return back()->with('error', 'Bu mahallede ilanlar bulunduğu için silinemez.');
        }

        $neighborhood->delete();

        return redirect()->route('admin.locations.neighborhoods.index')
            ->with('success', 'Mahalle başarıyla silindi.');
    }

    /**
     * Get neighborhoods by district (for AJAX)
     */
    public function byDistrict(District $district)
    {
        $neighborhoods = $district->neighborhoods()->where('is_active', true)->orderBy('name')->get();
        return response()->json($neighborhoods);
    }
}
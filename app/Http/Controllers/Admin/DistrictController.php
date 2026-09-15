<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DistrictController extends Controller
{
    /**
     * Display a listing of districts.
     */
    public function index(Request $request)
    {
        $query = District::with('city')->withCount(['neighborhoods', 'listings']);

        // Filter by city if provided
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $districts = $query->orderBy('name')->paginate(20);
        $cities = City::orderBy('name')->get();

        return view('admin.locations.districts.index', compact('districts', 'cities'));
    }

    /**
     * Show the form for creating a new district.
     */
    public function create(Request $request)
    {
        $cities = City::orderBy('name')->get();
        $selectedCityId = $request->get('city_id');

        return view('admin.locations.districts.create', compact('cities', 'selectedCityId'));
    }

    /**
     * Store a newly created district in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'is_active' => 'boolean'
        ]);

        // Check for unique name within the same city
        $exists = District::where('city_id', $request->city_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'name' => 'Bu şehirde aynı isimde bir ilçe zaten mevcut.'
            ]);
        }

        District::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'city_id' => $request->city_id,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.locations.districts.index')
            ->with('success', 'İlçe başarıyla eklendi.');
    }

    /**
     * Display the specified district.
     */
    public function show(District $district)
    {
        $district->load(['city', 'neighborhoods', 'listings']);
        return view('admin.locations.districts.show', compact('district'));
    }

    /**
     * Show the form for editing the specified district.
     */
    public function edit(District $district)
    {
        $cities = City::orderBy('name')->get();
        return view('admin.locations.districts.edit', compact('district', 'cities'));
    }

    /**
     * Update the specified district in storage.
     */
    public function update(Request $request, District $district)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'is_active' => 'boolean'
        ]);

        // Check for unique name within the same city (excluding current district)
        $exists = District::where('city_id', $request->city_id)
            ->where('name', $request->name)
            ->where('id', '!=', $district->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'name' => 'Bu şehirde aynı isimde bir ilçe zaten mevcut.'
            ]);
        }

        $district->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'city_id' => $request->city_id,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.locations.districts.index')
            ->with('success', 'İlçe başarıyla güncellendi.');
    }

    /**
     * Remove the specified district from storage.
     */
    public function destroy(District $district)
    {
        // Check if district has neighborhoods
        if ($district->neighborhoods()->count() > 0) {
            return back()->with('error', 'Bu ilçede mahalleler bulunduğu için silinemez. Önce mahalleleri silin.');
        }

        // Check if district has listings
        if ($district->listings()->count() > 0) {
            return back()->with('error', 'Bu ilçede ilanlar bulunduğu için silinemez.');
        }

        $district->delete();

        return redirect()->route('admin.locations.districts.index')
            ->with('success', 'İlçe başarıyla silindi.');
    }

    /**
     * Get districts by city (for AJAX)
     */
    public function byCity(City $city)
    {
        $districts = $city->districts()->where('is_active', true)->orderBy('name')->get();
        return response()->json($districts);
    }
}
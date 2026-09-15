<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of cities.
     */
    public function index()
    {
        $cities = City::withCount(['districts', 'listings'])->orderBy('name')->paginate(20);
        return view('admin.locations.cities.index', compact('cities'));
    }

    /**
     * Show the form for creating a new city.
     */
    public function create()
    {
        return view('admin.locations.cities.create');
    }

    /**
     * Store a newly created city in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:cities,name',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $upload = $this->fileUploadService->uploadFile($request->file('image'), 'image', 'cities');
            $imagePath = $upload['path'];
        }

        City::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'image' => $imagePath,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.locations.cities.index')
            ->with('success', 'Şehir başarıyla eklendi.');
    }

    /**
     * Display the specified city.
     */
    public function show(City $city)
    {
        $city->load(['districts.neighborhoods', 'listings']);
        return view('admin.locations.cities.show', compact('city'));
    }

    /**
     * Show the form for editing the specified city.
     */
    public function edit(City $city)
    {
        return view('admin.locations.cities.edit', compact('city'));
    }

    /**
     * Update the specified city in storage.
     */
    public function update(Request $request, City $city)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:cities,name,' . $city->id,
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($city->image) {
                $this->fileUploadService->deleteFile($city->image);
            }

            $upload = $this->fileUploadService->uploadFile($request->file('image'), 'image', 'cities');
            $data['image'] = $upload['path'];
        }

        $city->update($data);

        return redirect()->route('admin.locations.cities.index')
            ->with('success', 'Şehir başarıyla güncellendi.');
    }

    /**
     * Remove the specified city from storage.
     */
    public function destroy(City $city)
    {
        // Check if city has districts
        if ($city->districts()->count() > 0) {
            return back()->with('error', 'Bu şehirde ilçeler bulunduğu için silinemez. Önce ilçeleri silin.');
        }

        // Check if city has listings
        if ($city->listings()->count() > 0) {
            return back()->with('error', 'Bu şehirde ilanlar bulunduğu için silinemez.');
        }

        $city->delete();

        return redirect()->route('admin.locations.cities.index')
            ->with('success', 'Şehir başarıyla silindi.');
    }
}
<?php

namespace App\Repositories;

use App\Models\City;
use App\Models\District;
use App\Models\Neighborhood;
use Illuminate\Support\Collection;

class LocationRepository extends BaseRepository
{
    public function model(): string
    {
        return City::class;
    }

    public function getAllCities(): Collection
    {
        return City::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getDistrictsByCity(int $cityId): Collection
    {
        return District::where('city_id', $cityId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getNeighborhoodsByDistrict(int $districtId): Collection
    {
        return Neighborhood::where('district_id', $districtId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}

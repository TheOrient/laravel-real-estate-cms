<?php

namespace App\Services;

use App\Repositories\LocationRepository;
use Illuminate\Support\Collection;

class LocationService implements ServiceInterface
{
    protected $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function getAllCities(): Collection
    {
        return $this->locationRepository->getAllCities();
    }

    public function getDistrictsByCity(int $cityId): Collection
    {
        return $this->locationRepository->getDistrictsByCity($cityId);
    }

    public function getNeighborhoodsByDistrict(int $districtId): Collection
    {
        return $this->locationRepository->getNeighborhoodsByDistrict($districtId);
    }
}
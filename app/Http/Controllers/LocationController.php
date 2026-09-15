<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Get districts by city ID.
     */
    public function getDistrictsByCity(int $city): JsonResponse
    {
        $districts = $this->locationService->getDistrictsByCity($city);
        return response()->json($districts);
    }

    /**
     * Get neighborhoods by district ID.
     */
    public function getNeighborhoodsByDistrict(int $district): JsonResponse
    {
        $neighborhoods = $this->locationService->getNeighborhoodsByDistrict($district);
        return response()->json($neighborhoods);
    }
}

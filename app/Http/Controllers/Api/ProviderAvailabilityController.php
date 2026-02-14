<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProviderAvailabilityService;
use App\Models\User;

class ProviderAvailabilityController extends Controller
{
    public function __construct(
        private ProviderAvailabilityService $availabilityService
    ) {}

    public function availableDates(User $provider)
    {
        $dates = $this->availabilityService->getAvailableDates($provider);

        return response()->json([
            'data' => $dates
        ]);
    }
    public function availableSlots(Request $request, User $provider)
    {
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required|exists:services,id',
        ]);

        $slots = $this->availabilityService->getAvailableSlots(
            $provider,
            $request->date,
            $request->service_id
        );

        return response()->json([
            'data' => $slots
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

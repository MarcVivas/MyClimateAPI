<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTemperatureRequest;
use App\Http\Resources\TemperatureResource;
use App\Services\SensorService;
use App\Services\TemperatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemperatureController extends Controller
{
    protected $temperatureService;
    protected $sensorService;

    public function __construct(SensorService $sensorService, TemperatureService $temperatureService){
        $this->sensorService = $sensorService;
        $this->temperatureService = $temperatureService;

    }

    /**
     * Store a temperature
     * @param CreateTemperatureRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function store(CreateTemperatureRequest $request, int $id){
        // User is authenticated --> Checked by sanctum middleware
        $user = Auth::user();

        $sensor = $this->sensorService->findByIdOrFail($id);

        // Return 403 if the user is not the owner of the sensor
        abort_if($sensor->home->user_id !== $user->id, 403);

        // Get the data validated
        $validatedData = $request->validated();

        // Append sensor id
        $validatedData['sensor_id'] = $sensor->id;

        // Register the new temperature
        $temperature =  $this->temperatureService->createTemperature($validatedData);

        // Return 201 and the new temperature
        return response()->json(['data' => new TemperatureResource($temperature)], 201);
    }
}

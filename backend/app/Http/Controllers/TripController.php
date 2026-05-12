<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Events\TripCreated;
use App\Events\TripEnded;
use App\Events\TripLocationUpdated;
use App\Events\TripStarted;
use Illuminate\Http\Request;
  use App\Models\Trip;
class TripController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'destination_name' => 'required',
        ]);

        $trip = $request->user()->trips()->create($request->only([
            'origin',
            'destination',
            'destination_name',
        
        ]));

        \Log::info('Dispatching TripCreated event', ['trip_id' => $trip->id]);
        TripCreated::dispatch($trip, $request->user());

         return $trip;

       
    }

  

public function show(Request $request, Trip $trip)
{
    // Check if user owns the trip
    if ($trip->user_id == $request->user()->id) {
        return $trip;
    }

    // Check if driver owns the trip
    if ($trip->driver && $request->user()->driver) {
        if ($trip->driver->id == $request->user()->driver->id) {
            return $trip;
        }
    }

    return response()->json([
        'message' => 'You do not have access to this trip',
    ], 403);

}
public function accept (Request $request, $trip){

   $request->validate([
    'driver_location' => 'required',]);

    $trip->update([
        'driver_id' => $request->user()->driver->id,
        'driver_location' => $request->driver_location,
    ]);


    $trip->load('driver.user');
    TripAccepted::dispatch($trip, $trip->user);
    return $trip;
}


public function start (Request $request, $trip){

    $trip->update([
        'is_started' => true,
    ]);

    $trip->load('driver.user'); 
    TripStarted::dispatch($trip);
    return $trip;

}

public function end (Request $request, $trip){
     $trip->update([
        'is_completed' => true,
    ]);

    $trip->load('driver.user'); 
    TripEnded::dispatch($trip);
    return $trip;


}
 
public function location (Request $request, $trip){

    $request->validate([
        'driver_location' => 'required',
    ]); 

    $trip->update([
        'driver_location' => $request->driver_location,
    ]);

    $trip->load('driver.user');
    TripLocationUpdated::dispatch($trip, $trip->user);
    return $trip;   

}


}

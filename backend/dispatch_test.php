<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Trip;
use App\Models\User;
use App\Events\TripCreated;

$trip = Trip::first();
$user = User::first();

if (!$trip || !$user) {
    echo "No trip or user found\n";
    exit;
}

echo "Dispatching TripCreated for trip {$trip->id}...\n";
TripCreated::dispatch($trip, $user);
echo "Done.\n";

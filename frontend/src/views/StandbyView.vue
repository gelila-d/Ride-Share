<script setup>
import Loader from '@/components/Loader.vue'
import Echo from 'laravel-echo';
import { onMounted, computed } from 'vue';
import Pusher from 'pusher-js';
import { useTripStore } from '@/stores/trip';
import { GoogleMap, Marker, Polyline } from 'vue3-google-map';
import http from '@/helpers/http';
import { useRouter } from 'vue-router';

import { useLocationStore } from '@/stores/location';

const trip = useTripStore()
const title = computed(() => {
    if (trip.id) {
        return 'Ride requested '
    }
    return 'Waiting for ride request...'
})
const locationStore = useLocationStore()
const router = useRouter()
const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

window.Pusher = Pusher;

onMounted(() => {
    let echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        wssPort: import.meta.env.VITE_REVERB_PORT,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ["ws", "wss"],
    })

    console.log('Echo initialized, listening on drivers channel...');
    
    echo.connector.pusher.connection.bind('state_change', (states) => {
        console.log('Echo connection state:', states.current);
    });

    echo.channel('drivers').listen('.TripCreated', (e) => {
        console.log('TripCreated event received:', e);
        trip.$patch(e.trip)
        console.log('Updated trip store:', trip.id);
    })
})

const handleDecline = () => {
    console.log('Decline button clicked')
    trip.reset()
    console.log('Trip store reset, current trip.id:', trip.id)
}

const handleAccept = async () => {
    console.log('Accept button clicked, updating location...')
    await locationStore.updateCurrentLocation()
    console.log('Location updated:', locationStore.current.geometry)
    console.log('Sending accept POST request for trip ID:', trip.id)
    http().post(`/api/trip/${trip.id}/accept`, {
        driver_location: locationStore.current.geometry
    })
        .then((response) => {
            router.push({
                name: 'driving'
            })
        })
        .catch((error) => {
            console.error(error)
        })
}

</script>

<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4 text-center">{{ title }}</h1>
        <div v-if="!trip.id" class="mt-8 flex justify-center">
            <Loader />
        </div>
        <div v-else class="mt-8">
            <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
                <div class="bg-white px-4 py-5 sm:p-6">
                    <div>
                        <GoogleMap 
                            :api-key="apiKey"
                            :zoom="14"
                            :center="{ lat: parseFloat(trip.destination.lat), lng: parseFloat(trip.destination.lng) }"
                            style="width: 100%; height: 256px"
                        >
                            <Marker :options="{ position: { lat: parseFloat(trip.destination.lat), lng: parseFloat(trip.destination.lng) } }" />
                            <Marker v-if="trip.origin.lat" :options="{ position: { lat: parseFloat(trip.origin.lat), lng: parseFloat(trip.origin.lng) } }" />
                            <Polyline
                                v-if="trip.origin.lat && trip.destination.lat"
                                :options="{
                                    path: [
                                        { lat: parseFloat(trip.origin.lat), lng: parseFloat(trip.origin.lng) },
                                        { lat: parseFloat(trip.destination.lat), lng: parseFloat(trip.destination.lng) }
                                    ],
                                    geodesic: true,
                                    strokeColor: '#3B82F6',
                                    strokeOpacity: 0.8,
                                    strokeWeight: 4,
                                }"
                            />
                        </GoogleMap>
                    </div>
                    <div class="mt-4">
                        <p class="text-lg">Going to <strong>{{ trip.destination_name }}</strong></p>
                    </div>
                </div>
                <div class="flex justify-between bg-gray-50 px-4 py-3 text-right sm:px-6">
                    <button
                        @click="handleDecline"
                        class="inline-flex w-32 justify-center rounded-md border border-transparent bg-black text-white py-2 hover:bg-gray-800 transition-colors"
                    >
                        Decline
                    </button>
                    <button
                        @click="handleAccept"
                        class="inline-flex w-32 justify-center rounded-md border border-transparent bg-black text-white py-2 hover:bg-gray-800 transition-colors"
                    >
                        Accept
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
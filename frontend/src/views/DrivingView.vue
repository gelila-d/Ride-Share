<script setup>
import { useLocationStore } from '@/stores/location'
import { useTripStore } from '@/stores/trip'
import { useRouter } from 'vue-router'
import { ref, onMounted, onUnmounted } from 'vue'
import http from '@/helpers/http'
import { GoogleMap, Marker, Polyline } from 'vue3-google-map'

const locationStore = useLocationStore()
const trip = useTripStore()
const router = useRouter()
const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

const mapCenter = ref({ lat: 9.0054, lng: 38.7636 })
let interval = null

onMounted(() => {
    if (!trip.id) {
        router.push({ name: 'standby' })
        return
    }

    if (trip.origin.lat) {
        mapCenter.value = { lat: parseFloat(trip.origin.lat), lng: parseFloat(trip.origin.lng) }
    } else if (locationStore.current.geometry.lat) {
        mapCenter.value = { lat: locationStore.current.geometry.lat, lng: locationStore.current.geometry.lng }
    }

    // periodically update driver location
    interval = setInterval(async () => {
        await locationStore.updateCurrentLocation()
        if (trip.id) {
            http().post(`/api/trip/${trip.id}/location`, {
                driver_location: locationStore.current.geometry
            }).catch(console.error)
        }
    }, 10000)
})

onUnmounted(() => {
    if (interval) clearInterval(interval)
})

const handleStartTrip = () => {
    http().post(`/api/trip/${trip.id}/start`)
        .then(() => {
            trip.is_started = true
        })
        .catch((error) => console.error(error))
}

const handleCompleteTrip = () => {
    http().post(`/api/trip/${trip.id}/end`)
        .then(() => {
            trip.is_completed = true
            trip.reset()
            router.push({ name: 'standby' })
        })
        .catch((error) => console.error(error))
}
</script>

<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4 text-center">
            {{ trip.is_started ? 'Driving to destination...' : 'Driving to passenger...' }}
        </h1>

        <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
            <div class="bg-white px-4 py-5 sm:p-6">
                <div>
                    <GoogleMap 
                        :api-key="apiKey"
                        :zoom="14"
                        :center="mapCenter"
                        style="width: 100%; height: 256px"
                    >
                        <Marker v-if="locationStore.current.geometry.lat" :options="{ position: { lat: locationStore.current.geometry.lat, lng: locationStore.current.geometry.lng }, title: 'You' }" />
                        <Marker v-if="trip.origin.lat" :options="{ position: { lat: parseFloat(trip.origin.lat), lng: parseFloat(trip.origin.lng) }, title: 'Passenger' }" />
                        <Marker v-if="trip.destination.lat" :options="{ position: { lat: parseFloat(trip.destination.lat), lng: parseFloat(trip.destination.lng) }, title: 'Destination' }" />
                        
                        <!-- Route to Passenger -->
                        <Polyline
                            v-if="trip.origin.lat && locationStore.current.geometry.lat && !trip.is_started"
                            :options="{
                                path: [
                                    { lat: locationStore.current.geometry.lat, lng: locationStore.current.geometry.lng },
                                    { lat: parseFloat(trip.origin.lat), lng: parseFloat(trip.origin.lng) }
                                ],
                                geodesic: true,
                                strokeColor: '#3B82F6',
                                strokeOpacity: 0.8,
                                strokeWeight: 4
                            }"
                        />

                        <!-- Route to Destination -->
                        <Polyline
                            v-if="trip.destination.lat && locationStore.current.geometry.lat && trip.is_started"
                            :options="{
                                path: [
                                    { lat: locationStore.current.geometry.lat, lng: locationStore.current.geometry.lng },
                                    { lat: parseFloat(trip.destination.lat), lng: parseFloat(trip.destination.lng) }
                                ],
                                geodesic: true,
                                strokeColor: '#10B981',
                                strokeOpacity: 0.8,
                                strokeWeight: 4
                            }"
                        />
                    </GoogleMap>
                </div>
                <div class="mt-4">
                    <p class="text-lg">
                        {{ trip.is_started ? `Going to ${trip.destination_name}` : 'Picking up passenger' }}
                    </p>
                </div>
            </div>
            <div class="flex justify-between bg-gray-50 px-4 py-3 sm:px-6">
                <button
                    v-if="!trip.is_started"
                    @click="handleStartTrip"
                    class="w-full inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-gray-800 transition-colors focus:outline-none"
                >
                    Start Trip
                </button>
                <button
                    v-else
                    @click="handleCompleteTrip"
                    class="w-full inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-gray-800 transition-colors focus:outline-none"
                >
                    Complete Trip
                </button>
            </div>
        </div>
    </div>
</template>

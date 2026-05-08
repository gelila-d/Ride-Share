<script setup>
import { useLocationStore } from '@/stores/location'
import { useRouter } from 'vue-router'
import { GoogleMap, Marker, Polyline } from 'vue3-google-map';

const locationStore = useLocationStore()
const router = useRouter()

const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY
import { onMounted, computed } from 'vue'

onMounted(async () => {
    await locationStore.updateCurrentLocation()
})

const mapCenter = computed(() => {
    if (locationStore.destination.geometry.lat && locationStore.destination.geometry.lng) {
        return { lat: locationStore.destination.geometry.lat, lng: locationStore.destination.geometry.lng }
    } else if (locationStore.current.geometry.lat && locationStore.current.geometry.lng) {
        return { lat: locationStore.current.geometry.lat, lng: locationStore.current.geometry.lng }
    }
    return { lat: 9.0054, lng: 38.7636 } // Fallback to Addis Ababa
})

const currentPosition = computed(() => {
    if (locationStore.current.geometry.lat && locationStore.current.geometry.lng) {
        return { lat: locationStore.current.geometry.lat, lng: locationStore.current.geometry.lng }
    }
    return null
})

const destinationPosition = computed(() => {
    if (locationStore.destination.geometry.lat && locationStore.destination.geometry.lng) {
        return { lat: locationStore.destination.geometry.lat, lng: locationStore.destination.geometry.lng }
    }
    return null
})
</script>

<template>
  <div class="pt-16">
    <h1 class="text-3xl font-semibold mb-4 text-center">Here's your trip</h1>
    <div>
      <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
        <div class="bg-white px-4 py-5 sm:p-6">
            <div>
                <GoogleMap 
                    :api-key="apiKey"
                    :zoom="14"
                    :center="mapCenter"
                    style="width: 100%; height: 256px"
                >
                    <Marker v-if="currentPosition" :options="{ position: currentPosition, title: 'Current Location' }" />
                    <Marker v-if="destinationPosition" :options="{ position: destinationPosition, title: 'Destination' }" />
                    <Polyline
                        v-if="currentPosition && destinationPosition"
                        :options="{
                            path: [currentPosition, destinationPosition],
                            geodesic: true,
                            strokeColor: '#3B82F6',
                            strokeOpacity: 0.8,
                            strokeWeight: 4,
                        }"
                    />
                </GoogleMap>
            </div>
          <div class="mt-4">
            <p class="text-lg">Going to <strong class="text-black">{{ locationStore.destination.name || 'Unknown Destination' }}</strong></p>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
          <button
            @click="router.push({ name: 'trip' })"
            class="inline-flex w-32 justify-center rounded-md border border-transparent bg-black text-white py-2 hover:bg-gray-800 transition-colors"
          >
            Let's Go!
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

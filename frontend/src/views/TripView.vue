<script setup>
import { useLocationStore } from '@/stores/location'
import { useRouter } from 'vue-router'
import { GoogleMap, Marker } from 'vue3-google-map'
import { onMounted, computed } from 'vue'

const locationStore = useLocationStore()
const router = useRouter()
const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

onMounted(async () => {
    await locationStore.updateCurrentLocation()
})

const mapCenter = computed(() => {
    if (locationStore.destination.geometry.lat && locationStore.destination.geometry.lng) {
        return { lat: locationStore.destination.geometry.lat, lng: locationStore.destination.geometry.lng }
    } else if (locationStore.current.geometry.lat && locationStore.current.geometry.lng) {
        return { lat: locationStore.current.geometry.lat, lng: locationStore.current.geometry.lng }
    }
    return { lat: 9.0054, lng: 38.7636 }
})
</script>

<template>
  <div class="pt-16">
    <h1 class="text-3xl font-semibold mb-4 text-center">Waiting on a driver...</h1>
    <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
      <div class="bg-white px-4 py-5 sm:p-6">
        <div>
          <GoogleMap 
              :api-key="apiKey"
              :zoom="14"
              :center="mapCenter"
              style="width: 100%; height: 256px"
          >
              <Marker v-if="locationStore.destination.geometry.lat" :options="{ position: mapCenter }" />
          </GoogleMap>
        </div>
        <div class="mt-4">
          <p class="text-lg">Going to <strong class="text-black">{{ locationStore.destination.name || 'Unknown Destination' }}</strong></p>
        </div>
      </div>

      <!-- Driver status section -->
      <div class="bg-gray-50 px-4 py-4 sm:px-6 text-center">
        <div class="flex items-center justify-center space-x-2 text-gray-600">
          <svg class="animate-spin h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span class="text-sm font-medium">Looking for a nearby driver...</span>
        </div>
      </div>
    </div>
  </div>
</template>
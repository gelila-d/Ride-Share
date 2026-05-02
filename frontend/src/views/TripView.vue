<script setup>
import { useLocationStore } from '@/stores/location'
import { useRouter } from 'vue-router'
import { GoogleMap, Marker } from 'vue3-google-map'
import { onMounted, computed, ref } from 'vue'

const locationStore = useLocationStore()
const router = useRouter()
const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

const driverAccepted = ref(false)

// Mock driver info (replace with real data from backend later)
const driver = ref({
    name: '',
    car: '',
    plate: '',
    rating: 0,
    photo: ''
})

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

// Estimate distance (in km) between current location and destination using Haversine formula
const estimatedDistance = computed(() => {
    if (!currentPosition.value || !destinationPosition.value) return null
    const R = 6371
    const dLat = (destinationPosition.value.lat - currentPosition.value.lat) * Math.PI / 180
    const dLng = (destinationPosition.value.lng - currentPosition.value.lng) * Math.PI / 180
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(currentPosition.value.lat * Math.PI / 180) * Math.cos(destinationPosition.value.lat * Math.PI / 180) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2)
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
    return (R * c).toFixed(1)
})

// Estimate time (assuming average 30 km/h in city traffic)
const estimatedTime = computed(() => {
    if (!estimatedDistance.value) return null
    const minutes = Math.round((estimatedDistance.value / 30) * 60)
    if (minutes < 1) return '< 1 min'
    return `${minutes} min`
})

// Estimate price (using a simple rate: base 50 ETB + 15 ETB/km)
const estimatedPrice = computed(() => {
    if (!estimatedDistance.value) return null
    const price = 50 + (estimatedDistance.value * 15)
    return Math.round(price)
})

const handleCancelTrip = () => {
    router.push({ name: 'landing' })
}
</script>

<template>
  <div class="pt-16">
    <h1 class="text-3xl font-semibold mb-4 text-center">
      {{ driverAccepted ? 'Driver is on the way!' : 'Waiting on a driver...' }}
    </h1>
    <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
      <!-- Map section -->
      <div class="bg-white px-4 py-5 sm:p-6">
        <div>
          <GoogleMap 
              :api-key="apiKey"
              :zoom="13"
              :center="mapCenter"
              style="width: 100%; height: 256px"
          >
              <!-- Current location marker (blue) -->
              <Marker 
                  v-if="currentPosition" 
                  :options="{ 
                      position: currentPosition,
                      icon: {
                          path: 0,
                          scale: 8,
                          fillColor: '#4285F4',
                          fillOpacity: 1,
                          strokeColor: '#ffffff',
                          strokeWeight: 2
                      },
                      title: 'Your location'
                  }" 
              />
              <!-- Destination marker (red) -->
              <Marker 
                  v-if="destinationPosition" 
                  :options="{ 
                      position: destinationPosition,
                      title: locationStore.destination.name || 'Destination'
                  }" 
              />
          </GoogleMap>
        </div>

        <!-- Trip details -->
        <div class="mt-4 space-y-2">
          <p class="text-lg">Going to <strong class="text-black">{{ locationStore.destination.name || 'Unknown Destination' }}</strong></p>
          
          <div v-if="estimatedDistance" class="flex justify-between text-sm text-gray-600 pt-2 border-t border-gray-100">
            <div class="flex items-center space-x-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
              <span>{{ estimatedDistance }} km</span>
            </div>
            <div class="flex items-center space-x-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>~{{ estimatedTime }}</span>
            </div>
            <div class="flex items-center space-x-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
              </svg>
              <span>{{ estimatedPrice }} ETB</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Driver info (shown when driver accepts) -->
      <div v-if="driverAccepted" class="bg-white px-4 py-4 sm:px-6 border-t border-gray-200">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-semibold text-gray-900">{{ driver.name }}</p>
            <p class="text-sm text-gray-500">{{ driver.car }} · {{ driver.plate }}</p>
          </div>
          <div class="text-right">
            <div class="flex items-center space-x-1 text-yellow-500">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span class="text-sm font-medium text-gray-700">{{ driver.rating }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Waiting for driver (shown while searching) -->
      <div v-else class="bg-gray-50 px-4 py-4 sm:px-6 text-center border-t border-gray-100">
        <div class="flex items-center justify-center space-x-2 text-gray-600">
          <svg class="animate-spin h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span class="text-sm font-medium">Looking for a nearby driver...</span>
        </div>
      </div>

      <!-- Cancel button -->
      <div class="bg-white px-4 py-3 sm:px-6 text-center border-t border-gray-200">
        <button
            @click="handleCancelTrip"
            class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-6 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
        >
          Cancel Trip
        </button>
      </div>
    </div>
  </div>
</template>
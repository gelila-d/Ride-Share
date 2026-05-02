<script setup>
import { useLocationStore } from '@/stores/location'
import { useRouter } from 'vue-router'
import { GoogleMap, Marker } from 'vue3-google-map'
import { onMounted, computed, ref } from 'vue'

const locationStore = useLocationStore()
const router = useRouter()
const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

const driverAccepted = ref(false)

const driver = ref({
    name: 'Abebe Kebede',
    car: 'Toyota Yaris · White',
    plate: 'AA-3-12345',
    rating: 4.8
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

const estimatedTime = computed(() => {
    if (!estimatedDistance.value) return null
    const minutes = Math.round((estimatedDistance.value / 30) * 60)
    if (minutes < 1) return '< 1 min'
    return `${minutes} min`
})

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
  <div class="pt-16 pb-8">
    <!-- Header -->
    <h1 class="text-3xl font-semibold mb-6 text-center">
      {{ driverAccepted ? 'Driver is on the way!' : 'Waiting on a driver...' }}
    </h1>

    <div class="max-w-sm mx-auto space-y-4 px-4">

      <!-- Map Card -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <GoogleMap
            :api-key="apiKey"
            :zoom="13"
            :center="mapCenter"
            style="width: 100%; height: 220px"
        >
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
            <Marker
                v-if="destinationPosition"
                :options="{
                    position: destinationPosition,
                    title: locationStore.destination.name || 'Destination'
                }"
            />
        </GoogleMap>
      </div>

      <!-- Trip Info Card -->
      <div class="bg-white rounded-xl shadow p-5">
        <!-- From / To -->
        <div class="space-y-3">
          <div class="flex items-start space-x-3">
            <div class="mt-1 w-3 h-3 rounded-full bg-blue-500 ring-4 ring-blue-100 flex-shrink-0"></div>
            <div>
              <p class="text-xs text-gray-400 uppercase tracking-wide">Pickup</p>
              <p class="text-sm font-medium text-gray-800">Current Location</p>
            </div>
          </div>
          <div class="ml-1.5 border-l-2 border-dashed border-gray-200 h-4"></div>
          <div class="flex items-start space-x-3">
            <div class="mt-1 w-3 h-3 rounded-full bg-black ring-4 ring-gray-200 flex-shrink-0"></div>
            <div>
              <p class="text-xs text-gray-400 uppercase tracking-wide">Destination</p>
              <p class="text-sm font-medium text-gray-800">{{ locationStore.destination.name || 'Unknown' }}</p>
            </div>
          </div>
        </div>

        <!-- Stats Row -->
        <div v-if="estimatedDistance" class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-3 gap-2 text-center">
          <div>
            <p class="text-lg font-bold text-gray-900">{{ estimatedDistance }}<span class="text-xs font-normal text-gray-400"> km</span></p>
            <p class="text-xs text-gray-400">Distance</p>
          </div>
          <div class="border-l border-r border-gray-100">
            <p class="text-lg font-bold text-gray-900">{{ estimatedTime }}</p>
            <p class="text-xs text-gray-400">Est. Time</p>
          </div>
          <div>
            <p class="text-lg font-bold text-gray-900">{{ estimatedPrice }}<span class="text-xs font-normal text-gray-400"> ETB</span></p>
            <p class="text-xs text-gray-400">Est. Price</p>
          </div>
        </div>
      </div>

      <!-- Driver Card (when accepted) -->
      <div v-if="driverAccepted" class="bg-white rounded-xl shadow p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Your Driver</p>
        <div class="flex items-center space-x-4">
          <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 truncate">{{ driver.name }}</p>
            <p class="text-sm text-gray-500 truncate">{{ driver.car }}</p>
            <p class="text-xs text-gray-400">{{ driver.plate }}</p>
          </div>
          <div class="flex items-center space-x-1 bg-yellow-50 px-2 py-1 rounded-lg">
            <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span class="text-sm font-semibold text-gray-700">{{ driver.rating }}</span>
          </div>
        </div>
      </div>

      <!-- Searching Indicator (when waiting) -->
      <div v-else class="bg-white rounded-xl shadow p-5">
        <div class="flex items-center space-x-3">
          <div class="relative">
            <div class="w-10 h-10 rounded-full border-2 border-gray-200 border-t-black animate-spin"></div>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-800">Looking for a nearby driver</p>
            <p class="text-xs text-gray-400">This usually takes a few seconds...</p>
          </div>
        </div>
      </div>

      <!-- Cancel Button -->
      <button
          @click="handleCancelTrip"
          class="w-full py-3 rounded-xl border border-gray-300 text-sm font-medium text-gray-600 bg-white shadow hover:bg-gray-50 transition-colors"
      >
        Cancel Trip
      </button>
    </div>
  </div>
</template>
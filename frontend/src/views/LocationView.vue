<script setup>
import { ref, watch } from 'vue'
import { GoogleMap } from 'vue3-google-map'
import { useLocationStore } from '@/stores/location'
import { useRouter } from 'vue-router'

const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY
const mapCenter = { lat: 9.0054, lng: 38.7636 } // Default to Addis Ababa, Ethiopia
const mapRef = ref(null)

const locationStore = useLocationStore()
const router = useRouter()

// Use a local ref for the input to guarantee reactivity
const destinationInput = ref(locationStore.destination.name || '')
const searchResults = ref([])
const isSearching = ref(false)

let searchTimeout = null

const handleInput = () => {
    // Sync to store
    locationStore.destination.name = destinationInput.value
    
    // clear selection if user types
    locationStore.destination.geometry.lat = null
    locationStore.destination.geometry.lng = null
    
    if (searchTimeout) clearTimeout(searchTimeout)
    
    if (!destinationInput.value.trim()) {
        searchResults.value = []
        return
    }

    searchTimeout = setTimeout(async () => {
        isSearching.value = true
        try {
            // Biased towards Addis Ababa
            const res = await fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(destinationInput.value)}&lat=9.0054&lon=38.7636&limit=5`)
            const data = await res.json()
            searchResults.value = data.features || []
        } catch (error) {
            console.error('Error fetching autocomplete:', error)
        } finally {
            isSearching.value = false
        }
    }, 300)
}

const selectLocation = (feature) => {
    const props = feature.properties
    let name = props.name
    if (props.city && props.city !== props.name) {
        name += ', ' + props.city
    } else if (props.state && props.state !== props.name) {
        name += ', ' + props.state
    }
    
    // Explicitly update both the input and the store
    destinationInput.value = name
    locationStore.destination.name = name
    locationStore.destination.address = props.city || props.state || ''
    locationStore.destination.geometry.lat = feature.geometry.coordinates[1]
    locationStore.destination.geometry.lng = feature.geometry.coordinates[0]
    
    searchResults.value = []
}

const handleBlur = () => {
    // Give it more time so click/touch events can register properly
    setTimeout(() => {
        searchResults.value = []
    }, 300)
}

const handleGoToMap = () => {
    if (destinationInput.value) {
        // Fallback sync
        locationStore.destination.name = destinationInput.value
        router.push({ name: 'map' })
    }
}

</script>

<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4">Where are we going?</h1>
        <form action="#" @submit.prevent="handleGoToMap">
            <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left mb-6">
                <div class="bg-white px-4 py-5 sm:p-6">
                    <div class="relative">
                        <input type="text" placeholder="My destination"
                            v-model="destinationInput"
                            @input="handleInput"
                            @blur="handleBlur"
                            class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm focus:border-black focus:ring-black">
                        
                        <!-- Photon Autocomplete Dropdown -->
                        <div v-if="searchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200">
                            <ul class="max-h-60 overflow-auto py-1">
                                <li v-for="(result, index) in searchResults" :key="index"
                                    @click="selectLocation(result)"
                                    @mousedown.prevent="selectLocation(result)"
                                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                                    <div class="font-medium text-gray-900">{{ result.properties.name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ result.properties.city || result.properties.state || '' }}
                                        <template v-if="result.properties.country">
                                            {{ (result.properties.city || result.properties.state) ? ', ' : '' }}{{ result.properties.country }}
                                        </template>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-white hover:bg-gray-800 transition-colors">
                        Find A Ride
                    </button>
                </div>
            </div>
        </form>

        <div class="max-w-sm mx-auto shadow sm:rounded-md overflow-hidden">
            <GoogleMap 
                ref="mapRef"
                :api-key="apiKey" 
                style="width: 100%; height: 400px" 
                :center="mapCenter" 
                :zoom="11"
            >
            </GoogleMap>
        </div>
    </div>
</template>
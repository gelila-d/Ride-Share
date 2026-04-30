<script setup>
import { ref, onMounted } from 'vue'
import { GoogleMap } from 'vue3-google-map'

const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY
const mapCenter = { lat: 40.7128, lng: -74.0060 } // Default to New York
const mapRef = ref(null)
const destinationInput = ref(null)
let autocomplete = null

const handleMapReady = () => {
    if (window.google && window.google.maps && window.google.maps.places) {
        autocomplete = new window.google.maps.places.Autocomplete(destinationInput.value, {
            fields: ['geometry', 'name', 'formatted_address']
        })

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace()
            if (place.geometry && place.geometry.location) {
                // Update map center to the selected place
                if (mapRef.value && mapRef.value.map) {
                    mapRef.value.map.panTo(place.geometry.location)
                    mapRef.value.map.setZoom(15)
                }
            }
        })
    }
}
</script>

<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4">Where are we going?</h1>
        <form action="#" @submit.prevent>
            <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left mb-6">
                <div class="bg-white px-4 py-5 sm:p-6">
                    <div>
                        <input type="text" placeholder="My destination"
                            ref="destinationInput"
                            class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm focus:border-black focus:ring-black">
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
                    <button
                        type="button"
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
                :libraries="['places']"
                style="width: 100%; height: 400px" 
                :center="mapCenter" 
                :zoom="11"
                @ready="handleMapReady"
            >
            </GoogleMap>
        </div>
    </div>
</template>
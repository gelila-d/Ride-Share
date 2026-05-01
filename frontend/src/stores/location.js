import { ref, computed, reactive } from 'vue'
import { defineStore } from 'pinia'

 const getUserLocation = async() => {
    return new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject)
    })
}


export const useLocationStore = defineStore('location', () => {
    const destination = reactive({
        name: '',
        address: '',
        geometry: {
            lat: null,
            lng: null
        }

    })

    const current = reactive({
        geometry:{
            lat: null,
            lng: null
        }
    })

    const updateCurrentLocation = async() => {
        try {
            const userLocation = await getUserLocation()
            current.geometry.lat = userLocation.coords.latitude
            current.geometry.lng = userLocation.coords.longitude
        } catch (error) {
            console.error('Error getting user location:', error)
        }
    }

    return { destination, current, updateCurrentLocation } 
})

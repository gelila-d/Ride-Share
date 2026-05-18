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
            const userLocation = await Promise.race([
                getUserLocation(),
                new Promise((resolve, reject) => setTimeout(() => reject(new Error('Location timeout')), 3000))
            ])
            current.geometry.lat = userLocation.coords.latitude
            current.geometry.lng = userLocation.coords.longitude
        } catch (error) {
            console.error('Error getting user location:', error)
            current.geometry.lat = 9.0102
            current.geometry.lng = 38.7615
        }
    }

    return { destination, current, updateCurrentLocation } 
})

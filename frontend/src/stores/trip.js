import { defineStore } from 'pinia'
import { reactive, ref } from "vue"



export const useTripStore = defineStore('trip', () => {
    const id = ref(null)
    const user_id = ref(null)
    const is_started = ref(false)
    const is_completed = ref(false)


    const origin = reactive({
        lat: null,
        lng: null
    })

    const destination = reactive({
        lat: null,
        lng: null
    })

    const destination_name = ref('')

    const reset = () => {
        id.value = null
        user_id.value = null
        is_started.value = false
        is_completed.value = false
        origin.lat = null
        origin.lng = null
        destination.lat = null
        destination.lng = null
        destination_name.value = ''
    }

    return { id, user_id, is_started, is_completed, origin, destination, destination_name, reset }
})
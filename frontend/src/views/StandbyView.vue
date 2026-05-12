<script setup>
import Loader from '@/components/Loader.vue'
import Echo from 'laravel-echo';
import { onMounted, ref } from 'vue';
import Pusher from 'pusher-js';
import { useTripStore } from '@/stores/trip';
const title= ref('Waiting for ride request...')
const trip= useTripStore()
window.Pusher = Pusher;
onMounted(()=>{
    let echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        wssPort: import.meta.env.VITE_REVERB_PORT,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ["ws", "wss"],
    })
    echo.channel('drivers').listen('TripCreated', (e) => {
        title.value='Ride requested'
        trip.$patch(e.trip)
        console.log('TripCreated',e);
    })
})

</script>

<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4">{{ title }}</h1>
        <div class=" mt-8 flex justify-center">
            <Loader />
        </div>

    </div>
</template>
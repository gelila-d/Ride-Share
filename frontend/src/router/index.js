import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import LandingView from '@/views/LandingView.vue'
import LocationView from '@/views/LocationView.vue'
import MapView from '@/views/MapView.vue'
import TripView from '@/views/TripView.vue'
import DriverView from '@/views/DriverView.vue'
import StandbyView from '@/views/StandbyView.vue'
import DrivingView from '@/views/DrivingView.vue'
import http from '@/helpers/http'
import { useLocationStore } from '@/stores/location'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/landing',
      name: 'landing',
      component: LandingView,
    },
    {
      path: '/location',
      name: 'location',
      component: LocationView,
    },
    {
      path: '/map',
      name: 'map',
      component: MapView,
    },
    {
      path: '/trip',
      name: 'trip',
      component: TripView,
    },
    {
      path: '/driver',
      name: 'driver',
      component: DriverView,
    },
    {
      path: '/standby',
      name: 'standby',
      component: StandbyView,
    },
    {
      path: '/driving',
      name: 'driving',
      component: DrivingView,
    }

  ],
})

router.beforeEach((to, from) => {
  if (to.name === 'login') {
    return true
  }
  if (!localStorage.getItem('token')) {
    return {
      name: 'login'
    }
  }

  if (to.name === 'map') {
    const locationStore = useLocationStore()
    if (!locationStore.destination.geometry.lat || !locationStore.destination.geometry.lng) {
      return { name: 'location' }
    }
  }

  checkTokenAuthenticity();

}
)




const checkTokenAuthenticity = () => {
  http().get('/api/user')
    .then(response => {
      // Token is valid
    })
    .catch(error => {
      console.error('Token authenticity check failed:', error)
      if (error.response && (error.response.status === 401 || error.response.status === 403)) {
        localStorage.removeItem('token')
        router.push({ name: 'login' })
      }
    })
}
export default router

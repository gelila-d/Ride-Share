import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import LoginVerifyView from '@/views/LoginVerifyView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/verify',
      name: 'login.verify',
      component: LoginVerifyView,
    }
  ],
})

export default router


import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import HomePage from '../views/HomePage.vue'
import LoginPage from '../views/LoginPage.vue'
import { state as securityState } from '@/stores/security/state'

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'home',
    component: HomePage,
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
  },
  {
    path: '/admin',
    name: 'admin',
    // code-splitting
    component: () =>
        import('../views/AdminPage.vue'),
    beforeEnter: (to, from, next) => {
      if (securityState.me.roles.includes('ROLE_USER')) {
        next()
      } else {
        next({ name: 'home' })
      }
    }
  },
  {
    path: '/about',
    name: 'about',
    // code-splitting
    component: () =>
      import('../views/AboutPage.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
})

export default router

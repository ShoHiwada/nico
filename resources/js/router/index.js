import { createRouter, createWebHistory } from 'vue-router'
import SpaTest from '../pages/SpaTest.vue'

const routes = [
  {
    path: '/spa-test',
    name: 'spa-test',
    component: SpaTest,
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router

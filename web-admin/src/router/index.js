import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../views/DashboardView.vue'
import JobsView from '../views/JobsView.vue'
import UsersView from '../views/UsersView.vue'
import JobDetailView from '../views/JobDetailView.vue'
import UserDetailView from '../views/UserDetailView.vue'
import TimeRegistration from '../views/TimeRegistration.vue'
import Invoicing from '../views/Invoicing.vue'
import ManagementDashboard from '../views/ManagementDashboard.vue'
import LoginView from '../views/LoginView.vue'
import AssignmentsView from '../views/AssignmentsView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/',
      name: 'dashboard',
      component: DashboardView,
      meta: { requiresAuth: true }
    },
    {
      path: '/time',
      name: 'time-registration',
      component: TimeRegistration,
      meta: { requiresAuth: true, roles: ['admin', 'worker', 'client'] }
    },
    {
      path: '/invoicing',
      name: 'invoicing',
      component: Invoicing,
      meta: { requiresAuth: true, roles: ['admin'] }
    },
    {
      path: '/analytics',
      name: 'management-dashboard',
      component: ManagementDashboard,
      meta: { requiresAuth: true, roles: ['admin'] }
    },
    {
      path: '/jobs',
      name: 'jobs',
      component: JobsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/assignments',
      name: 'assignments',
      component: AssignmentsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/jobs/:id',
      name: 'job-detail',
      component: JobDetailView,
      meta: { requiresAuth: true }
    },
    {
      path: '/users',
      name: 'users',
      component: UsersView,
      meta: { requiresAuth: true }
    },
    {
      path: '/users/:id',
      name: 'user-detail',
      component: UserDetailView,
      meta: { requiresAuth: true }
    },
  ],
})


router.beforeEach((to, from) => {
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  if (to.meta.requiresAuth && !user) {
    return { name: 'login' }
  }
  if (to.meta.roles && user && !to.meta.roles.includes(user.role)) {
    return { name: 'dashboard' }
  }
  return true
})

export default router

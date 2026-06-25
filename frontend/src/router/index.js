import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // ── Auth ────────────────────────────────────────────────────────────────
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { guest: true },
    },

    // ── Register ─────────────────────────────────────────────────────────────────
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue'),
      meta: { guest: true },
    },

    // ── App ─────────────────────────────────────────────────────────────────
    {
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: () => import('@/views/DashboardView.vue'),
        },
        {
          path: 'tickets',
          name: 'tickets',
          component: () => import('@/views/tickets/TicketsView.vue'),
        },
        {
          path: 'tickets/create',
          name: 'tickets.create',
          component: () => import('@/views/tickets/TicketCreateView.vue'),
        },
        {
          path: 'tickets/:id',
          name: 'tickets.show',
          component: () => import('@/views/tickets/TicketDetailView.vue'),
        },
        {
          path: 'users',
          name: 'users',
          component: () => import('@/views/users/UsersView.vue'),
          meta: { requiresRole: 'admin' },
        },
      ],
    },

    // ── Fallback ─────────────────────────────────────────────────────────────
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

// ── Guards ───────────────────────────────────────────────────────────────────
router.beforeEach((to) => {
  const auth = useAuthStore()

  // Route protégée — non connecté
  if (to.meta.requiresAuth && ! auth.isAuthenticated) {
    return { name: 'login' }
  }

  // Route guest — déjà connecté
  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  // Route avec rôle requis
  if (to.meta.requiresRole && auth.userRole !== to.meta.requiresRole) {
    return { name: 'dashboard' }
  }
})

export default router

<template>
  <div class="min-h-screen bg-gray-50 flex">

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center gap-3 px-6 border-b border-gray-200 shrink-0">
        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <span class="font-bold text-gray-900 text-lg">HelpDesk</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

        <RouterLink :to="{ name: 'dashboard' }" custom v-slot="{ isActive, navigate }">
          <button @click="navigate"
            :class="navClass(isActive)"
          >
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Tableau de bord</span>
          </button>
        </RouterLink>

        <RouterLink :to="{ name: 'tickets' }" custom v-slot="{ isActive, navigate }">
          <button @click="navigate"
            :class="navClass(isActive)"
          >
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
            <span>Tickets</span>
          </button>
        </RouterLink>

        <!-- Admin only -->
        <RouterLink v-if="authStore.isAdmin" :to="{ name: 'users' }" custom v-slot="{ isActive, navigate }">
          <button @click="navigate"
            :class="navClass(isActive)"
          >
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Utilisateurs</span>
          </button>
        </RouterLink>

      </nav>

      <!-- User info + logout -->
      <div class="p-4 border-t border-gray-200 shrink-0">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center shrink-0">
            <span class="text-primary-700 font-semibold text-sm">
              {{ userInitials }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ authStore.user?.name }}</p>
            <p class="text-xs text-gray-500 truncate capitalize">{{ authStore.userRole }}</p>
          </div>
        </div>
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Se déconnecter
        </button>
      </div>
    </aside>

    <!-- Overlay mobile -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-black/30 lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Contenu principal -->
    <div class="flex-1 flex flex-col min-w-0">

      <!-- Topbar -->
      <header class="h-16 bg-white border-b border-gray-200 flex items-center gap-4 px-6 shrink-0">
        <button
          class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100"
          @click="sidebarOpen = !sidebarOpen"
          aria-label="Ouvrir le menu"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <div class="flex-1">
          <h1 class="text-base font-semibold text-gray-900">{{ pageTitle }}</h1>
        </div>

        <RouterLink
          v-if="authStore.isAuthenticated"
          :to="{ name: 'tickets.create' }"
          class="btn-primary text-sm"
        >
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nouveau ticket
        </RouterLink>
      </header>

      <!-- Page content -->
      <main class="flex-1 p-6 overflow-auto">
        <RouterView />
      </main>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const authStore = useAuthStore()
const route     = useRoute()
const router    = useRouter()

const sidebarOpen = ref(false)

const userInitials = computed(() => {
  const name = authStore.user?.name ?? ''
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const pageTitles = {
  dashboard:      'Tableau de bord',
  tickets:        'Tickets',
  'tickets.show': 'Détail du ticket',
  'tickets.create': 'Nouveau ticket',
  users:          'Gestion des utilisateurs',
}

const pageTitle = computed(() => pageTitles[route.name] ?? 'HelpDesk')

function navClass(isActive) {
  return [
    'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200',
    isActive
      ? 'bg-primary-50 text-primary-700'
      : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
  ]
}

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>

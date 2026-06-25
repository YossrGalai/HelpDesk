<template>
  <div class="space-y-6">

    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.label" class="card p-5">
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm font-medium text-gray-500">{{ stat.label }}</span>
          <div :class="['w-9 h-9 rounded-lg flex items-center justify-center', stat.iconBg]">
            <component :is="stat.icon" :class="['w-5 h-5', stat.iconColor]" />
          </div>
        </div>
        <div class="text-2xl font-bold text-gray-900">
          <span v-if="loading" class="inline-block w-12 h-7 bg-gray-200 animate-pulse rounded" />
          <span v-else>{{ stat.value }}</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ stat.sub }}</p>
      </div>
    </div>

    <!-- Tickets récents -->
    <div class="card">
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-900">Tickets récents</h2>
        <RouterLink :to="{ name: 'tickets' }" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
          Voir tout →
        </RouterLink>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 5" :key="i" class="px-6 py-4 flex items-center gap-4">
          <div class="w-48 h-4 bg-gray-200 animate-pulse rounded" />
          <div class="w-20 h-5 bg-gray-200 animate-pulse rounded-full ml-auto" />
          <div class="w-20 h-5 bg-gray-200 animate-pulse rounded-full" />
        </div>
      </div>

      <!-- Erreur -->
      <div v-else-if="error" class="px-6 py-10 text-center">
        <p class="text-sm text-red-500">{{ error }}</p>
        <button @click="loadData" class="mt-3 text-sm text-primary-600 hover:underline">Réessayer</button>
      </div>

      <!-- Vide -->
      <div v-else-if="!recentTickets.length" class="px-6 py-12 text-center">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
        </svg>
        <p class="text-sm text-gray-400">Aucun ticket pour le moment.</p>
        <RouterLink :to="{ name: 'tickets.create' }" class="mt-2 inline-block text-sm text-primary-600 hover:underline">
          Créer le premier ticket
        </RouterLink>
      </div>

      <!-- Liste -->
      <div v-else class="divide-y divide-gray-100">
        <RouterLink
          v-for="ticket in recentTickets"
          :key="ticket.id"
          :to="{ name: 'tickets.show', params: { id: ticket.id } }"
          class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors duration-150 group"
        >
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate group-hover:text-primary-700">
              {{ ticket.title }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">
              {{ ticket.creator?.name }} · {{ formatDate(ticket.created_at) }}
            </p>
          </div>
          <StatusBadge :status="ticket.status" />
          <PriorityBadge :priority="ticket.priority" />
        </RouterLink>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios.js'
import StatusBadge from '@/components/tickets/StatusBadge.vue'
import PriorityBadge from '@/components/tickets/PriorityBadge.vue'


const loading       = ref(true)
const error         = ref(null)
const recentTickets = ref([])
const counts        = ref({ open: 0, in_progress: 0, closed: 0, total: 0 })

const stats = computed(() => [
  {
    label:     'Total tickets',
    value:     counts.value.total,
    sub:       'Tous statuts confondus',
    iconBg:    'bg-blue-50',
    iconColor: 'text-blue-600',
    icon:      ticketIcon(),
  },
  {
    label:     'Ouverts',
    value:     counts.value.open,
    sub:       'En attente de traitement',
    iconBg:    'bg-amber-50',
    iconColor: 'text-amber-600',
    icon:      clockIcon(),
  },
  {
    label:     'En cours',
    value:     counts.value.in_progress,
    sub:       'Actuellement traités',
    iconBg:    'bg-primary-50',
    iconColor: 'text-primary-600',
    icon:      progressIcon(),
  },
  {
    label:     'Résolus',
    value:     counts.value.closed,
    sub:       'Tickets fermés',
    iconBg:    'bg-green-50',
    iconColor: 'text-green-600',
    icon:      checkIcon(),
  },
])

async function loadData() {
  loading.value = true
  error.value   = null

  try {
    const { data } = await api.get('/tickets', { params: { per_page: 5 } })
    const tickets  = data.data ?? []
    recentTickets.value = tickets.slice(0, 5)

    counts.value.total       = data.meta?.total ?? tickets.length
    counts.value.open        = tickets.filter(t => t.status === 'OPEN').length
    counts.value.in_progress = tickets.filter(t => t.status === 'IN_PROGRESS').length
    counts.value.closed      = tickets.filter(t => t.status === 'CLOSED').length
  } catch {
    error.value = 'Impossible de charger les tickets.'
  } finally {
    loading.value = false
  }
}

function formatDate(dateStr) {
  if (! dateStr) return ''
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day:   '2-digit',
    month: 'short',
    year:  'numeric',
  })
}

// Icônes inline
function ticketIcon() {
  return { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z' })
  ])}
}
function clockIcon() {
  return { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' })
  ])}
}
function progressIcon() {
  return { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' })
  ])}
}
function checkIcon() {
  return { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2',
      d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' })
  ])}
}

onMounted(loadData)
</script>

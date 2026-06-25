<template>
  <div class="space-y-5">

    <!-- Filtres -->
    <div class="card p-4">
      <div class="flex flex-wrap gap-3 items-center">
        <select :value="store.filters.status" @change="store.setFilter('status', $event.target.value)" class="input-field w-auto text-sm">
          <option value="">Tous les statuts</option>
          <option value="OPEN">Ouvert</option>
          <option value="IN_PROGRESS">En cours</option>
          <option value="CLOSED">Fermé</option>
        </select>
        <select :value="store.filters.priority" @change="store.setFilter('priority', $event.target.value)" class="input-field w-auto text-sm">
          <option value="">Toutes les priorités</option>
          <option value="LOW">Faible</option>
          <option value="MEDIUM">Moyenne</option>
          <option value="HIGH">Haute</option>
          <option value="CRITICAL">Critique</option>
        </select>
        <button v-if="hasActiveFilters" @click="store.resetFilters()" class="btn-secondary text-sm">
          Réinitialiser
        </button>
        <span class="ml-auto text-sm text-gray-400">
          {{ store.pagination.total }} ticket{{ store.pagination.total > 1 ? 's' : '' }}
        </span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="card overflow-hidden">
      <div v-for="i in 8" :key="i" class="px-6 py-4 border-b border-gray-100 flex items-center gap-4">
        <div class="w-8 h-4 bg-gray-100 animate-pulse rounded" />
        <div class="flex-1 h-4 bg-gray-200 animate-pulse rounded" />
        <div class="w-20 h-6 bg-gray-100 animate-pulse rounded-full" />
        <div class="w-20 h-6 bg-gray-100 animate-pulse rounded-full" />
        <div class="w-24 h-4 bg-gray-100 animate-pulse rounded" />
      </div>
    </div>

    <!-- Erreur -->
    <div v-else-if="store.error" class="card px-6 py-12 text-center">
      <p class="text-sm text-red-500 mb-3">{{ store.error }}</p>
      <button @click="store.fetchTickets()" class="btn-secondary text-sm">Réessayer</button>
    </div>

    <!-- Vide -->
    <div v-else-if="!store.tickets.length" class="card px-6 py-16 text-center">
      <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
      </svg>
      <p class="text-gray-500 font-medium mb-1">Aucun ticket trouvé</p>
      <p class="text-sm text-gray-400 mb-4">
        {{ hasActiveFilters ? 'Aucun résultat pour ces filtres.' : 'Commencez par créer un ticket.' }}
      </p>
      <RouterLink v-if="!hasActiveFilters" :to="{ name: 'tickets.create' }" class="btn-primary text-sm">
        Créer un ticket
      </RouterLink>
      <button v-else @click="store.resetFilters()" class="btn-secondary text-sm">
        Réinitialiser les filtres
      </button>
    </div>

    <!-- ✅ Tableau -->
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-left">
              <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-16">#ID</th>
              <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Titre</th>
              <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-32">Statut</th>
              <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-32">Priorité</th>
              <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-36">Assigné à</th>
              <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-32">Date</th>
              <th class="px-4 py-3 w-10"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="ticket in store.tickets"
              :key="ticket.id"
              @click="goToTicket(ticket.id)"
              class="hover:bg-gray-50 transition-colors cursor-pointer group"
            >
              <td class="px-4 py-3.5">
                <span class="font-mono text-xs text-gray-400 font-medium">#{{ ticket.id }}</span>
              </td>
              <td class="px-4 py-3.5">
                <p class="font-medium text-gray-900 group-hover:text-primary-700 transition-colors truncate max-w-xs">
                  {{ ticket.title }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">par {{ ticket.creator?.name ?? '—' }}</p>
              </td>
              <td class="px-4 py-3.5">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', statusClass(ticket.status)]">
                  <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(ticket.status)" />
                  {{ statusLabel(ticket.status) }}
                </span>
              </td>
              <td class="px-4 py-3.5">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', priorityClass(ticket.priority)]">
                  {{ priorityLabel(ticket.priority) }}
                </span>
              </td>
              <td class="px-4 py-3.5">
                <div v-if="ticket.assigned_to" class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center shrink-0">
                    <span class="text-primary-700 text-xs font-semibold">
                      {{ initials(ticket.assigned_to?.name ?? ticket.assignee?.name) }}
                    </span>
                  </div>
                  <span class="text-gray-600 text-xs truncate max-w-[90px]">
                    {{ ticket.assigned_to?.name ?? ticket.assignee?.name ?? '—' }}
                  </span>
                </div>
                <span v-else class="text-gray-300 text-xs">Non assigné</span>
              </td>
              <td class="px-4 py-3.5">
                <span class="text-xs text-gray-400">{{ formatDate(ticket.created_at) }}</span>
              </td>
              <td class="px-4 py-3.5 text-right">
                <svg class="w-4 h-4 text-gray-300 group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="store.pagination.lastPage > 1" class="flex items-center justify-between">
      <p class="text-sm text-gray-500">
        Page {{ store.pagination.currentPage }} sur {{ store.pagination.lastPage }}
      </p>
      <div class="flex gap-2">
        <button :disabled="store.pagination.currentPage === 1" @click="store.setPage(store.pagination.currentPage - 1)" class="btn-secondary text-sm disabled:opacity-40">
          ← Précédent
        </button>
        <button :disabled="store.pagination.currentPage === store.pagination.lastPage" @click="store.setPage(store.pagination.currentPage + 1)" class="btn-secondary text-sm disabled:opacity-40">
          Suivant →
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useTicketStore } from '@/stores/tickets.js'

const store  = useTicketStore()
const router = useRouter()

const hasActiveFilters = computed(() =>
  store.filters.status || store.filters.priority || store.filters.assigned_to
)

function goToTicket(id) {
  router.push({ name: 'tickets.show', params: { id } })
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
}

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function statusLabel(s) { return { OPEN: 'Ouvert', IN_PROGRESS: 'En cours', CLOSED: 'Fermé' }[s] ?? s }
function statusClass(s)  { return { OPEN: 'bg-blue-50 text-blue-700', IN_PROGRESS: 'bg-amber-50 text-amber-700', CLOSED: 'bg-gray-100 text-gray-500' }[s] ?? 'bg-gray-100 text-gray-500' }
function statusDotClass(s) { return { OPEN: 'bg-blue-500', IN_PROGRESS: 'bg-amber-500', CLOSED: 'bg-gray-400' }[s] ?? 'bg-gray-400' }
function priorityLabel(p) { return { LOW: 'Faible', MEDIUM: 'Moyenne', HIGH: 'Haute', CRITICAL: 'Critique' }[p] ?? p }
function priorityClass(p) { return { LOW: 'bg-gray-100 text-gray-500', MEDIUM: 'bg-sky-50 text-sky-700', HIGH: 'bg-orange-50 text-orange-700', CRITICAL: 'bg-red-50 text-red-700' }[p] ?? 'bg-gray-100 text-gray-500' }

onMounted(() => store.fetchTickets())
</script>

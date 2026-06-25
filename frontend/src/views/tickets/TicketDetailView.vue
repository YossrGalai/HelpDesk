<template>
  <div class="max-w-4xl mx-auto space-y-5">

    <div v-if="store.loading" class="card p-6 space-y-4">
      <div class="w-1/2 h-6 bg-gray-200 animate-pulse rounded" />
      <div class="w-full h-4 bg-gray-100 animate-pulse rounded" />
    </div>

    <div v-else-if="store.error" class="card px-6 py-12 text-center">
      <p class="text-sm text-red-500 mb-3">{{ store.error }}</p>
      <button @click="load" class="btn-secondary text-sm">Réessayer</button>
    </div>

    <template v-else-if="store.ticket">

      <!-- En-tête -->
      <div class="card p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-2">
              <span class="text-xs text-gray-400 font-mono">#{{ store.ticket.id }}</span>
              <StatusBadge :status="store.ticket.status" />
              <PriorityBadge :priority="store.ticket.priority" />
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ store.ticket.title }}</h2>
          </div>

          <div class="shrink-0 flex flex-col items-end gap-2">
            <!-- ✅ Bouton fermer — uniquement admin et agent assigné -->
            <button v-if="canClose" @click="handleClose" :disabled="closing" class="btn-secondary text-sm">
              {{ closing ? 'Fermeture...' : 'Fermer le ticket' }}
            </button>
            <!-- Message pour les users normaux -->
            <p v-else-if="store.ticket.status !== 'CLOSED' && authStore.isUser" class="text-xs text-gray-400 italic text-right max-w-xs">
              Seuls les agents et administrateurs peuvent fermer un ticket.
            </p>
            <!-- Erreur fermeture (ex: délai 3 jours) -->
            <p v-if="closeError" class="text-xs text-red-500 text-right max-w-xs">{{ closeError }}</p>
          </div>
        </div>

        <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-wrap">{{ store.ticket.description }}</p>

        <div class="flex flex-wrap items-center gap-4 mt-5 pt-5 border-t border-gray-100 text-xs text-gray-400">
          <span>Créé par <span class="font-medium text-gray-600">{{ store.ticket.creator?.name }}</span></span>
          <span>·</span>
          <span>{{ formatDate(store.ticket.created_at) }}</span>
          <template v-if="store.ticket.assigned_to">
            <span>·</span>
            <span>Assigné à <span class="font-medium text-gray-600">{{ store.ticket.assigned_to?.name }}</span></span>
          </template>
          <span v-if="lastActivityInfo" class="ml-auto flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Dernière activité : {{ lastActivityInfo }}
          </span>
        </div>
      </div>

      <!-- Gestion admin -->
      <div v-if="authStore.isAdmin" class="card p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Gestion du ticket</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-2">Assigner à</label>
            <div class="flex gap-2">
              <select v-model="assigneeId" class="input-field text-sm flex-1">
                <option value="">Non assigné</option>
                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
              </select>
              <button @click="handleAssign" :disabled="assigning || !assigneeId" class="btn-primary text-sm shrink-0">
                {{ assigning ? '...' : 'Assigner' }}
              </button>
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-2">Priorité</label>
            <div class="flex gap-2">
              <select v-model="selectedPriority" class="input-field text-sm flex-1">
                <option value="LOW">Faible</option>
                <option value="MEDIUM">Moyenne</option>
                <option value="HIGH">Haute</option>
                <option value="CRITICAL">Critique</option>
              </select>
              <button @click="handlePriority" :disabled="updatingPriority" class="btn-primary text-sm shrink-0">
                {{ updatingPriority ? '...' : 'Appliquer' }}
              </button>
            </div>
          </div>
        </div>
        <p v-if="adminFeedback" :class="['text-xs mt-3', adminFeedback.ok ? 'text-green-600' : 'text-red-500']">
          {{ adminFeedback.message }}
        </p>
      </div>

      <!-- Commentaires -->
      <div class="card">
        <div class="px-6 py-4 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-900">
            Commentaires <span class="ml-1 text-gray-400 font-normal">({{ comments.length }})</span>
          </h3>
        </div>

        <div v-if="comments.length" class="divide-y divide-gray-100">
          <div v-for="comment in comments" :key="comment.id" class="px-6 py-4 flex gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center shrink-0">
              <span class="text-primary-700 text-xs font-semibold">{{ initials(comment.author?.name) }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-sm font-medium text-gray-900">{{ comment.author?.name }}</span>
                <span class="text-xs text-gray-400">{{ formatDate(comment.created_at) }}</span>
              </div>
              <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ comment.comment }}</p>
            </div>
          </div>
        </div>

        <div v-else class="px-6 py-8 text-center">
          <p class="text-sm text-gray-400">Aucun commentaire pour le moment.</p>
        </div>

        <div v-if="store.ticket.status !== 'CLOSED'" class="px-6 py-4 border-t border-gray-100">
          <div class="flex gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center shrink-0">
              <span class="text-primary-700 text-xs font-semibold">{{ initials(authStore.user?.name) }}</span>
            </div>
            <div class="flex-1">
              <textarea
                v-model="commentBody"
                rows="3"
                placeholder="Ajouter un commentaire..."
                :class="['input-field resize-none text-sm', commentError ? 'input-error' : '']"
              />
              <p v-if="commentError" class="mt-1 text-xs text-red-500">{{ commentError }}</p>
              <div class="flex justify-end mt-2">
                <button @click="handleComment" :disabled="sendingComment" class="btn-primary text-sm">
                  {{ sendingComment ? 'Envoi...' : 'Commenter' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="px-6 py-4 border-t border-gray-100 text-center">
          <p class="text-xs text-gray-400">Ce ticket est fermé — les commentaires sont désactivés.</p>
        </div>
      </div>

    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useTicketStore } from '@/stores/tickets.js'
import { useAuthStore } from '@/stores/auth.js'
import { usersApi } from '@/api/users.js'
import api from '@/api/axios.js'
import StatusBadge from '@/components/tickets/StatusBadge.vue'
import PriorityBadge from '@/components/tickets/PriorityBadge.vue'

const route     = useRoute()
const store     = useTicketStore()
const authStore = useAuthStore()

const users            = ref([])
const assigneeId       = ref('')
const selectedPriority = ref('')
const assigning        = ref(false)
const updatingPriority = ref(false)
const closing          = ref(false)
const closeError       = ref('')
const adminFeedback    = ref(null)
const comments         = ref([])
const commentBody      = ref('')
const commentError     = ref('')
const sendingComment   = ref(false)

// ✅ Seuls admin et agent assigné peuvent fermer
const canClose = computed(() => {
  const t = store.ticket
  if (!t || t.status === 'CLOSED') return false
  if (authStore.isAdmin) return true
  if (authStore.isAgent) return t.assigned_to?.id === authStore.user?.id
  return false
})

const lastActivityInfo = computed(() => {
  if (!comments.value.length) return null
  const last = comments.value[comments.value.length - 1]
  const days = Math.floor((Date.now() - new Date(last.created_at).getTime()) / 86400000)
  if (days === 0) return "aujourd'hui"
  if (days === 1) return 'il y a 1 jour'
  return `il y a ${days} jours`
})

async function load() {
  await store.fetchTicket(route.params.id)
  if (store.ticket) {
    selectedPriority.value = store.ticket.priority
    assigneeId.value       = store.ticket.assigned_to?.id ?? ''
    await loadComments()
  }
  if (authStore.isAdmin) loadUsers()
}

async function loadComments() {
  try {
    const { data } = await api.get(`/tickets/${route.params.id}/comments`)
    comments.value = data.data ?? []
  } catch { /* silencieux */ }
}

async function loadUsers() {
  try {
    const { data } = await usersApi.list()
    users.value = data.data
  } catch { /* silencieux */ }
}

async function handleAssign() {
  assigning.value = true
  adminFeedback.value = null
  const result = await store.assignTicket(route.params.id, assigneeId.value)
  adminFeedback.value = result.success
    ? { ok: true, message: 'Ticket assigné avec succès.' }
    : { ok: false, message: "Erreur lors de l'assignation." }
  assigning.value = false
}

async function handlePriority() {
  updatingPriority.value = true
  adminFeedback.value = null
  const result = await store.updatePriority(route.params.id, selectedPriority.value)
  adminFeedback.value = result.success
    ? { ok: true, message: 'Priorité mise à jour.' }
    : { ok: false, message: 'Erreur lors de la mise à jour.' }
  updatingPriority.value = false
}

async function handleClose() {
  closing.value = true
  closeError.value = ''
  const result = await store.closeTicket(route.params.id)
  if (!result.success) {
    closeError.value = result.message || 'Impossible de fermer ce ticket pour le moment.'
  }
  closing.value = false
}

async function handleComment() {
  commentError.value = ''
  if (!commentBody.value.trim()) {
    commentError.value = 'Le commentaire ne peut pas être vide.'
    return
  }
  sendingComment.value = true
  try {
    const { data } = await api.post(`/tickets/${route.params.id}/comments`, { comment: commentBody.value.trim() })
    comments.value.push({ ...data.data, author: { id: authStore.user.id, name: authStore.user.name } })
    commentBody.value = ''
  } catch {
    commentError.value = "Erreur lors de l'envoi du commentaire."
  } finally {
    sendingComment.value = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

onMounted(load)
</script>

<template>
  <div class="space-y-5">

    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-gray-900">Gestion des utilisateurs</h2>
        <p class="text-sm text-gray-500 mt-0.5">Assignez et retirez des rôles aux membres de l'équipe.</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card overflow-hidden">
      <div v-for="i in 5" :key="i" class="px-6 py-4 border-b border-gray-100 flex items-center gap-4">
        <div class="w-10 h-10 bg-gray-200 animate-pulse rounded-full" />
        <div class="flex-1 space-y-2">
          <div class="w-1/3 h-4 bg-gray-200 animate-pulse rounded" />
          <div class="w-1/2 h-3 bg-gray-100 animate-pulse rounded" />
        </div>
      </div>
    </div>

    <!-- Erreur -->
    <div v-else-if="error" class="card px-6 py-12 text-center">
      <p class="text-sm text-red-500 mb-3">{{ error }}</p>
      <button @click="loadUsers" class="btn-secondary text-sm">Réessayer</button>
    </div>

    <!-- Tableau -->
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-left">
              <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                Utilisateur
              </th>
              <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-48">
                Rôle actuel
              </th>
              <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-72">
                Actions
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
              <!-- Avatar + infos -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center shrink-0">
                    <span class="text-primary-700 text-sm font-semibold">{{ initials(user.name) }}</span>
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                  </div>
                </div>
              </td>

              <!-- Rôles actuels -->
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="role in user.roles"
                    :key="role.id ?? role"
                    :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold', roleBadgeClass(roleName(role))]"
                  >
                    {{ roleName(role) }}
                  </span>
                  <span v-if="!user.roles?.length" class="text-xs text-gray-400 italic">Aucun rôle</span>
                </div>
              </td>

              <!-- ✅ Select + boutons — :value/@change pour forcer la réactivité -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <select
                    :value="selectedRoles[user.id]"
                    @change="selectedRoles[user.id] = $event.target.value"
                    class="input-field text-sm w-36"
                  >
                    <option value="" disabled>Rôle...</option>
                    <option value="admin">Admin</option>
                    <option value="agent">Agent</option>
                    <option value="user">User</option>
                  </select>

                  <button
                    @click="handleAssign(user)"
                    :disabled="!selectedRoles[user.id] || loadingUsers[user.id]"
                    class="btn-primary text-sm shrink-0"
                  >
                    {{ loadingUsers[user.id] === 'assign' ? '...' : 'Assigner' }}
                  </button>

                  <button
                    @click="handleRemove(user)"
                    :disabled="!selectedRoles[user.id] || loadingUsers[user.id]"
                    class="btn-secondary text-sm shrink-0"
                  >
                    {{ loadingUsers[user.id] === 'remove' ? '...' : 'Retirer' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Toast -->
    <Transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="toast"
        :class="[
          'fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2',
          toast.ok ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
        ]"
      >
        <svg v-if="toast.ok" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        {{ toast.message }}
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { usersApi } from '@/api/users.js'

const users        = ref([])
const loading      = ref(false)
const error        = ref(null)
const loadingUsers = reactive({})
const toast        = ref(null)

// ✅ ref (pas reactive) + initialisation en bloc après loadUsers
const selectedRoles = ref({})

async function loadUsers() {
  loading.value = true
  error.value   = null

  try {
    const { data } = await usersApi.list()
    users.value = data.data

    // Initialiser toutes les clés d'un coup → Vue détecte le changement
    const init = {}
    users.value.forEach(u => { init[u.id] = '' })
    selectedRoles.value = init

  } catch (err) {
    // ✅ Ne pas laisser un 401 silencieux — afficher un message utile
    if (err.response?.status === 403) {
      error.value = 'Accès refusé. Vous devez être administrateur.'
    } else {
      error.value = 'Impossible de charger les utilisateurs.'
    }
  } finally {
    loading.value = false
  }
}

async function handleAssign(user) {
  const role = selectedRoles.value[user.id]
  if (!role) return

  loadingUsers[user.id] = 'assign'
  try {
    const { data } = await usersApi.assignRole(user.id, role)
    const idx = users.value.findIndex(u => u.id === user.id)
    if (idx !== -1) users.value[idx].roles = data.data.roles
    showToast(true, `Rôle "${role}" assigné à ${user.name}.`)
    selectedRoles.value[user.id] = ''
  } catch {
    showToast(false, "Erreur lors de l'assignation du rôle.")
  } finally {
    loadingUsers[user.id] = null
  }
}

async function handleRemove(user) {
  const role = selectedRoles.value[user.id]
  if (!role) return

  loadingUsers[user.id] = 'remove'
  try {
    const { data } = await usersApi.removeRole(user.id, role)
    const idx = users.value.findIndex(u => u.id === user.id)
    if (idx !== -1) users.value[idx].roles = data.data.roles
    showToast(true, `Rôle "${role}" retiré de ${user.name}.`)
    selectedRoles.value[user.id] = ''
  } catch {
    showToast(false, "Erreur lors de la suppression du rôle.")
  } finally {
    loadingUsers[user.id] = null
  }
}

function roleName(role) {
  return typeof role === 'string' ? role : role?.name ?? ''
}

function roleBadgeClass(role) {
  const map = {
    admin: 'bg-purple-100 text-purple-700',
    agent: 'bg-blue-100 text-blue-700',
    user:  'bg-gray-100 text-gray-600',
  }
  return map[role] ?? 'bg-gray-100 text-gray-600'
}

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

let toastTimer = null
function showToast(ok, message) {
  toast.value = { ok, message }
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value = null }, 3000)
}

onMounted(loadUsers)
</script>

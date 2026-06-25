<template>
  <div class="max-w-2xl mx-auto">
    <div class="card p-6">

      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Nouveau ticket</h2>
        <p class="text-sm text-gray-500 mt-1">Décrivez votre problème avec le plus de détails possible.</p>
      </div>

      <!-- Erreur globale -->
      <div v-if="globalError" class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-red-700 text-sm">{{ globalError }}</p>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-5" novalidate>

        <!-- Titre -->
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">
            Titre <span class="text-red-500">*</span>
          </label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            placeholder="Résumez le problème en une phrase"
            :class="['input-field', errors.title ? 'input-error' : '']"
          />
          <p v-if="errors.title" class="mt-1.5 text-xs text-red-600">{{ errors.title }}</p>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">
            Description <span class="text-red-500">*</span>
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="5"
            placeholder="Décrivez le problème en détail : contexte, étapes pour reproduire, impact..."
            :class="['input-field resize-none', errors.description ? 'input-error' : '']"
          />
          <p v-if="errors.description" class="mt-1.5 text-xs text-red-600">{{ errors.description }}</p>
        </div>

        <!-- Priorité -->
        <div>
          <label for="priority" class="block text-sm font-medium text-gray-700 mb-1.5">
            Priorité <span class="text-red-500">*</span>
          </label>
          <select
            id="priority"
            v-model="form.priority"
            :class="['input-field', errors.priority ? 'input-error' : '']"
          >
            <option value="">Sélectionner une priorité</option>
            <option value="LOW">Faible — problème mineur, pas urgent</option>
            <option value="MEDIUM">Moyenne — impact modéré</option>
            <option value="HIGH">Haute — bloque une fonctionnalité</option>
            <option value="CRITICAL">Critique — système en panne</option>
          </select>
          <p v-if="errors.priority" class="mt-1.5 text-xs text-red-600">{{ errors.priority }}</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
          <button
            type="button"
            @click="router.back()"
            class="btn-secondary"
          >
            Annuler
          </button>
          <button
            type="submit"
            :disabled="store.loading"
            class="btn-primary"
          >
            <svg v-if="store.loading"
              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
              fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ store.loading ? 'Création...' : 'Créer le ticket' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useTicketStore } from '@/stores/tickets.js'

const router = useRouter()
const store  = useTicketStore()

const globalError = ref('')

const form = reactive({
  title:       '',
  description: '',
  priority:    '',
})

const errors = reactive({
  title:       '',
  description: '',
  priority:    '',
})

function validate() {
  errors.title       = ''
  errors.description = ''
  errors.priority    = ''
  let valid = true

  if (! form.title.trim()) {
    errors.title = 'Le titre est obligatoire.'
    valid = false
  } else if (form.title.trim().length < 5) {
    errors.title = 'Le titre doit contenir au moins 5 caractères.'
    valid = false
  }

  if (! form.description.trim()) {
    errors.description = 'La description est obligatoire.'
    valid = false
  } else if (form.description.trim().length < 10) {
    errors.description = 'La description doit contenir au moins 10 caractères.'
    valid = false
  }

  if (! form.priority) {
    errors.priority = 'La priorité est obligatoire.'
    valid = false
  }

  return valid
}

async function handleSubmit() {
  globalError.value = ''
  if (! validate()) return

  const result = await store.createTicket({
    title:       form.title.trim(),
    description: form.description.trim(),
    priority:    form.priority,
  })

  if (result.success) {
    router.push({ name: 'tickets.show', params: { id: result.ticket.id } })
  } else {
    // Erreurs de validation backend
    if (result.errors?.title)       errors.title       = result.errors.title[0]
    if (result.errors?.description) errors.description = result.errors.description[0]
    if (result.errors?.priority)    errors.priority    = result.errors.priority[0]
    if (! Object.keys(result.errors ?? {}).length) {
      globalError.value = result.message ?? 'Une erreur est survenue.'
    }
  }
}
</script>

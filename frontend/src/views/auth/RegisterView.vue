<template>
  <div class="min-h-screen bg-gray-50 flex">

    <!-- Panneau gauche — branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-primary-700 flex-col justify-between p-12">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <span class="text-white font-semibold text-lg">HelpDesk</span>
      </div>

      <div>
        <h1 class="text-4xl font-bold text-white leading-tight mb-4">
          Rejoignez l'équipe<br />support.
        </h1>
        <p class="text-primary-200 text-lg leading-relaxed">
          Créez votre compte et commencez à soumettre et suivre vos tickets en quelques secondes.
        </p>
      </div>

      <div class="grid grid-cols-3 gap-6">
        <div>
          <div class="text-3xl font-bold text-white">98%</div>
          <div class="text-primary-300 text-sm mt-1">Taux de résolution</div>
        </div>
        <div>
          <div class="text-3xl font-bold text-white">&lt;2h</div>
          <div class="text-primary-300 text-sm mt-1">Temps de réponse</div>
        </div>
        <div>
          <div class="text-3xl font-bold text-white">24/7</div>
          <div class="text-primary-300 text-sm mt-1">Disponibilité</div>
        </div>
      </div>
    </div>

    <!-- Panneau droit — formulaire -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
      <div class="w-full max-w-md">

        <!-- Header mobile -->
        <div class="flex items-center gap-2 mb-10 lg:hidden">
          <div class="w-7 h-7 bg-primary-600 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <span class="text-gray-900 font-semibold">HelpDesk</span>
        </div>

        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900">Créer un compte</h2>

        </div>

        <!-- Erreur globale -->
        <div v-if="authStore.error"
          class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-red-700 text-sm">{{ authStore.error }}</p>
        </div>

        <!-- Formulaire -->
        <form @submit.prevent="handleRegister" class="space-y-5" novalidate>

          <!-- Nom -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
              Nom complet <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              autocomplete="name"
              placeholder="Jean Dupont"
              :class="['input-field', errors.name ? 'input-error' : '']"
            />
            <p v-if="errors.name" class="mt-1.5 text-xs text-red-600">{{ errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
              Adresse e-mail <span class="text-red-500">*</span>
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              placeholder="vous@exemple.com"
              :class="['input-field', errors.email ? 'input-error' : '']"
            />
            <p v-if="errors.email" class="mt-1.5 text-xs text-red-600">{{ errors.email }}</p>
          </div>

          <!-- Mot de passe -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
              Mot de passe <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                placeholder="••••••••"
                :class="['input-field pr-10', errors.password ? 'input-error' : '']"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600"
                :aria-label="showPassword ? 'Masquer' : 'Afficher'"
              >
                <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="mt-1.5 text-xs text-red-600">{{ errors.password }}</p>
            <p class="mt-1.5 text-xs text-gray-400">Minimum 8 caractères.</p>
          </div>

          <!-- Confirmation mot de passe -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
              Confirmer le mot de passe <span class="text-red-500">*</span>
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="new-password"
              placeholder="••••••••"
              :class="['input-field', errors.password_confirmation ? 'input-error' : '']"
            />
            <p v-if="errors.password_confirmation" class="mt-1.5 text-xs text-red-600">
              {{ errors.password_confirmation }}
            </p>
          </div>

          <!-- Bouton submit -->
          <button
            type="submit"
            :disabled="authStore.loading"
            class="btn-primary w-full py-2.5 mt-2"
          >
            <svg v-if="authStore.loading"
              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
              fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ authStore.loading ? 'Création...' : 'Créer mon compte' }}
          </button>

        </form>

        <!-- Lien login -->
        <p class="mt-6 text-center text-sm text-gray-400">
          Déjà un compte ?
          <RouterLink :to="{ name: 'login' }" class="text-primary-600 hover:text-primary-700 font-medium">
            Se connecter
          </RouterLink>
        </p>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const router    = useRouter()
const authStore = useAuthStore()

const showPassword = ref(false)

const form = reactive({
  name:                  '',
  email:                 '',
  password:              '',
  password_confirmation: '',
})

const errors = reactive({
  name:                  '',
  email:                 '',
  password:              '',
  password_confirmation: '',
})

function validate() {
  errors.name                  = ''
  errors.email                 = ''
  errors.password              = ''
  errors.password_confirmation = ''
  let valid = true

  if (! form.name.trim()) {
    errors.name = 'Le nom est obligatoire.'
    valid = false
  } else if (form.name.trim().length < 2) {
    errors.name = 'Le nom doit contenir au moins 2 caractères.'
    valid = false
  }

  if (! form.email) {
    errors.email = 'L\'adresse e-mail est obligatoire.'
    valid = false
  } else if (! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Adresse e-mail invalide.'
    valid = false
  }

  if (! form.password) {
    errors.password = 'Le mot de passe est obligatoire.'
    valid = false
  } else if (form.password.length < 8) {
    errors.password = 'Le mot de passe doit contenir au moins 8 caractères.'
    valid = false
  }

  if (! form.password_confirmation) {
    errors.password_confirmation = 'Veuillez confirmer votre mot de passe.'
    valid = false
  } else if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Les mots de passe ne correspondent pas.'
    valid = false
  }

  return valid
}

async function handleRegister() {
  if (! validate()) return

  const result = await authStore.register({
    name:                  form.name.trim(),
    email:                 form.email,
    password:              form.password,
    password_confirmation: form.password_confirmation,
  })

  if (result.success) {
    router.push({ name: 'dashboard' })
  } else {
    if (result.errors?.name)     errors.name     = result.errors.name[0]
    if (result.errors?.email)    errors.email    = result.errors.email[0]
    if (result.errors?.password) errors.password = result.errors.password[0]
  }
}
</script>

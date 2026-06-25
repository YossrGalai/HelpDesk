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
          Support simplifié,<br />résolution rapide.
        </h1>
        <p class="text-primary-200 text-lg leading-relaxed">
          Gérez vos tickets, suivez les priorités et collaborez avec votre équipe depuis un seul endroit.
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
          <h2 class="text-2xl font-bold text-gray-900">Connexion</h2>
          <p class="text-gray-500 mt-1 text-sm">Entrez vos identifiants pour accéder à votre espace.</p>
        </div>

        <!-- ✅ Message d'erreur — affiché quand loginError est défini -->
        <Transition
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div
            v-if="loginError"
            class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3"
          >
            <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-red-700 text-sm">{{ loginError }}</p>
          </div>
        </Transition>

        <form @submit.prevent="handleLogin" class="space-y-5" novalidate>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
              Adresse e-mail
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              placeholder="vous@exemple.com"
              @input="loginError = ''"
              :class="['input-field', errors.email ? 'input-error' : '']"
            />
            <p v-if="errors.email" class="mt-1.5 text-xs text-red-600">{{ errors.email }}</p>
          </div>

          <!-- Mot de passe -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
              Mot de passe
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="••••••••"
                @input="loginError = ''"
                :class="['input-field pr-10', errors.password ? 'input-error' : '']"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600"
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
          </div>

          <button
            type="submit"
            :disabled="authStore.loading"
            class="btn-primary w-full py-2.5 mt-2 flex items-center justify-center"
          >
            <svg v-if="authStore.loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ authStore.loading ? 'Connexion...' : 'Se connecter' }}
          </button>

          <p class="mt-6 text-center text-sm text-gray-400">
            Pas encore de compte ?
            <RouterLink :to="{ name: 'register' }" class="text-primary-600 hover:text-primary-700 font-medium">
              Créer un compte
            </RouterLink>
          </p>

        </form>
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
const loginError   = ref('')   // ✅ erreur locale — indépendante du store

const form = reactive({ email: '', password: '' })
const errors = reactive({ email: '', password: '' })

function validate() {
  errors.email    = ''
  errors.password = ''
  let valid = true

  if (!form.email) {
    errors.email = "L'adresse e-mail est obligatoire."
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Adresse e-mail invalide.'
    valid = false
  }

  if (!form.password) {
    errors.password = 'Le mot de passe est obligatoire.'
    valid = false
  } else if (form.password.length < 6) {
    errors.password = 'Le mot de passe doit contenir au moins 6 caractères.'
    valid = false
  }

  return valid
}

async function handleLogin() {
  loginError.value = ''
  if (!validate()) return

  const result = await authStore.login(form)

  if (result.success) {
    router.push({ name: 'dashboard' })
  } else {
    // ✅ Lire depuis result.message (store) — l'intercepteur ne redirige
    // plus sur /auth/login grâce au check d'URL dans axios.js
    loginError.value =
      result.message ||
      authStore.error ||
      'Email ou mot de passe incorrect. Veuillez réessayer.'
  }
}
</script>

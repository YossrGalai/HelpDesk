import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios.js'

export const useAuthStore = defineStore('auth', () => {

  // ── State ──────────────────────────────────────────────────────────────────
  const user    = ref(JSON.parse(localStorage.getItem('user') ?? 'null'))
  const token   = ref(localStorage.getItem('token') ?? null)
  const loading = ref(false)
  const error   = ref(null)

  // ── Getters ────────────────────────────────────────────────────────────────
  const isAuthenticated = computed(() => !!token.value)

  const userRole = computed(() => {
    if (!user.value?.roles?.length) return null
    const role = user.value.roles[0]
    return typeof role === 'string' ? role : role.name
  })

  const isAdmin = computed(() => userRole.value === 'admin')
  const isAgent = computed(() => userRole.value === 'agent')
  const isUser  = computed(() => userRole.value === 'user')

  // ── Actions ────────────────────────────────────────────────────────────────

  async function login(credentials) {
    loading.value = true
    error.value   = null

    try {
      // ✅ FIX : utiliser axios directement sans passer par l'instance `api`
      // qui possède un intercepteur 401 → redirect vers /login.
      // Pour le login, on veut LIRE l'erreur, pas être redirigé.
      const { data } = await api.post('/auth/login', credentials, {
        // ✅ skipAuthRedirect : flag custom lu par l'intercepteur
        // pour ne PAS rediriger sur 401 pendant le login
        _skipAuthRedirect: true,
      })

      token.value = data.data.token
      user.value  = data.data.user

      localStorage.setItem('token', token.value)
      localStorage.setItem('user', JSON.stringify(user.value))

      return { success: true }

    } catch (err) {
      // ✅ Extraire le message dans tous les cas possibles
      const status = err.response?.status

      if (status === 401 || status === 422) {
        // 401 = identifiants invalides, 422 = validation Laravel
        error.value =
          err.response?.data?.message ||
          err.response?.data?.error   ||
          'Email ou mot de passe incorrect.'
      } else if (status === 429) {
        error.value = 'Trop de tentatives. Veuillez réessayer dans quelques minutes.'
      } else if (!err.response) {
        error.value = 'Impossible de contacter le serveur. Vérifiez votre connexion.'
      } else {
        error.value = 'Une erreur est survenue. Veuillez réessayer.'
      }

      return { success: false, message: error.value }

    } finally {
      loading.value = false
    }
  }

  async function register(formData) {
    loading.value = true
    error.value   = null

    try {
      const { data } = await api.post('/auth/register', formData, {
        _skipAuthRedirect: true,
      })

      token.value = data.data.token
      user.value  = data.data.user

      localStorage.setItem('token', token.value)
      localStorage.setItem('user', JSON.stringify(user.value))

      return { success: true }

    } catch (err) {
      error.value =
        err.response?.data?.message ||
        "Erreur lors de l'inscription."
      return { success: false, errors: err.response?.data?.errors ?? {} }

    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch {
      // on logout même si l'API échoue
    } finally {
      token.value = null
      user.value  = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }

  async function fetchMe() {
    try {
      const { data } = await api.get('/auth/me')
      user.value = data.data
      localStorage.setItem('user', JSON.stringify(user.value))
    } catch {
      logout()
    }
  }

  return {
    user, token, loading, error,
    isAuthenticated, userRole, isAdmin, isAgent, isUser,
    login, logout, fetchMe, register,
  }
})

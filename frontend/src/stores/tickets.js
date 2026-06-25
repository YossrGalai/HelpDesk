import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import api from '@/api/axios.js'

export const useTicketStore = defineStore('tickets', () => {

  // ── State ──────────────────────────────────────────────────────────────────
  const tickets    = ref([])
  const ticket     = ref(null)
  const loading    = ref(false)
  const error      = ref(null)
  const filters    = reactive({ status: '', priority: '', assigned_to: '' })
  const pagination = reactive({
    currentPage: 1,
    lastPage:    1,
    perPage:     15,
    total:       0,
  })

  // ── Actions ────────────────────────────────────────────────────────────────

  async function fetchTickets() {
    loading.value = true
    error.value   = null

    try {
      const params = {}
      if (filters.status)      params.status      = filters.status
      if (filters.priority)    params.priority    = filters.priority
      if (filters.assigned_to) params.assigned_to = filters.assigned_to
      params.page = pagination.currentPage

      const { data } = await api.get('/tickets', { params })

      tickets.value = data.data

      if (data.meta) {
        pagination.currentPage = data.meta.current_page
        pagination.lastPage    = data.meta.last_page
        pagination.perPage     = data.meta.per_page
        pagination.total       = data.meta.total
      }
    } catch (err) {
      error.value = err.response?.data?.message ?? 'Erreur lors du chargement des tickets.'
    } finally {
      loading.value = false
    }
  }

  async function fetchTicket(id) {
    loading.value = true
    error.value   = null

    try {
      const { data } = await api.get(`/tickets/${id}`)
      ticket.value = data.data
    } catch (err) {
      error.value = err.response?.data?.message ?? 'Ticket introuvable.'
    } finally {
      loading.value = false
    }
  }

  async function createTicket(ticketData) {
    loading.value = true
    error.value = null

    try {
      const { data } = await api.post('/tickets', ticketData)

      return {
        success: true,
        ticket: data.data,
      }
    } catch (err) {
      return {
        success: false,
        message: err.response?.data?.message,
        errors: err.response?.data?.errors,
      }
    } finally {
      loading.value = false
    }
  }

  async function closeTicket(id) {
    try {
      const { data } = await api.patch(`/tickets/${id}/close`)

      if (ticket.value && ticket.value.id === Number(id)) {
        ticket.value = data.data
      }

      return { success: true }
    } catch (err) {
      const message =
        err.response?.data?.message ||
        'Impossible de fermer ce ticket.'

      return { success: false, message }
    }
  }

  async function assignTicket(id, assigneeId) {
    try {
      const { data } = await api.patch(`/tickets/${id}/assign`, { assigned_to: assigneeId })

      if (ticket.value && ticket.value.id === Number(id)) {
        ticket.value = data.data
      }

      return { success: true }
    } catch (err) {
      return { success: false, message: err.response?.data?.message }
    }
  }

  async function updatePriority(id, priority) {
    try {
      const { data } = await api.patch(`/tickets/${id}/priority`, { priority })

      if (ticket.value && ticket.value.id === Number(id)) {
        ticket.value = data.data
      }

      return { success: true }
    } catch (err) {
      return { success: false, message: err.response?.data?.message }
    }
  }

  function setFilter(key, value) {
    filters[key]           = value
    pagination.currentPage = 1
    fetchTickets()
  }

  function resetFilters() {
    filters.status         = ''
    filters.priority       = ''
    filters.assigned_to    = ''
    pagination.currentPage = 1
    fetchTickets()
  }

  function setPage(page) {
    pagination.currentPage = page
    fetchTickets()
  }

  return {
    tickets, ticket, loading, error, filters, pagination,
    fetchTickets, fetchTicket,createTicket,
    closeTicket, assignTicket, updatePriority,
    setFilter, resetFilters, setPage,
  }
})

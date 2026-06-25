import api from '@/api/axios.js'

export const ticketsApi = {

  list(params = {}) {
    return api.get('/tickets', { params })
  },

  show(id) {
    return api.get(`/tickets/${id}`)
  },

  create(data) {
    return api.post('/tickets', data)
  },

  update(id, data) {
    return api.patch(`/tickets/${id}`, data)
  },

  close(id) {
    return api.patch(`/tickets/${id}/close`)
  },

  assign(id, userId) {
    return api.patch(`/tickets/${id}/assign`, { assigned_to: userId })
  },

  setPriority(id, priority) {
    return api.patch(`/tickets/${id}/priority`, { priority })
  },
}

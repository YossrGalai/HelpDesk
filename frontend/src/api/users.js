import api from '@/api/axios.js'

export const usersApi = {

  list() {
    return api.get('/users')
  },

  assignRole(userId, role) {
    return api.post(`/users/${userId}/roles`, { role })
  },

  removeRole(userId, role) {
    return api.delete(`/users/${userId}/roles/${role}`)
  },
}

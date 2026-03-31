import { defineStore } from 'pinia'
import api from '../js/api'

export const useUserStore = defineStore('user', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    children: [],
    stats: {},
    token: localStorage.getItem('token') || null,
    sessionType: localStorage.getItem('session_type') || null,
    selectedChildId: localStorage.getItem('selectedChildId') || null,
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    selectedChild: (state) => state.children.find((child) => child.id === state.selectedChildId) || null,
  },

  actions: {
    setAuth(user, token) {
      this.user = user
      this.token = token
      this.sessionType = user?.is_admin ? 'admin' : 'user'
      localStorage.setItem('user', JSON.stringify(user))
      localStorage.setItem('token', token)
      localStorage.setItem('session_type', this.sessionType)
    },

    setSelectedChild(childId) {
      this.selectedChildId = childId ? String(childId) : null
      if (this.selectedChildId) {
        localStorage.setItem('selectedChildId', String(this.selectedChildId))
      } else {
        localStorage.removeItem('selectedChildId')
      }
    },

    async fetchUser() {
      if (!this.token || this.loading) return

      try {
        this.loading = true

        const res = await api.get('/profile')
        this.user = res.data.user || res.data
        this.children = res.data.children || []
        this.stats = res.data.stats || {}
        this.sessionType = this.user?.is_admin ? 'admin' : 'user'
        localStorage.setItem('user', JSON.stringify(this.user))
        localStorage.setItem('session_type', this.sessionType)

        if (!this.selectedChildId && this.children.length) {
          this.setSelectedChild(this.children[0].id)
        }

        if (this.selectedChildId && !this.children.some((child) => child.id === this.selectedChildId)) {
          this.setSelectedChild(this.children[0]?.id || null)
        }
      } catch (error) {
        if (error.response?.status === 401) {
          this.logout()
        }
      } finally {
        this.loading = false
      }
    },

    async refreshChildren() {
      if (!this.token) return

      const { data } = await api.get('/profile/children')
      this.children = data

      if (!this.selectedChildId && this.children.length) {
        this.setSelectedChild(this.children[0].id)
      }
    },

    logout() {
      this.user = null
      this.children = []
      this.stats = {}
      this.token = null
      this.sessionType = null
      this.selectedChildId = null
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      localStorage.removeItem('session_type')
      localStorage.removeItem('selectedChildId')
    },
  },
})

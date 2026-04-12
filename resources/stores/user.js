import { defineStore } from 'pinia'
import api from '../js/api'
import { hasAdminAccess } from '../js/adminAccess'

export const useUserStore = defineStore('user', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    children: [],
    stats: {},
    summary: {},
    onboarding: null,
    currentTask: null,
    notifications: [],
    notificationsUnread: 0,
    token: localStorage.getItem('token') || null,
    sessionType: localStorage.getItem('session_type') || null,
    selectedChildId: localStorage.getItem('selectedChildId') || null,
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    selectedChild: (state) => state.children.find((child) => child.id === state.selectedChildId) || null,
    hasUnreadNotifications: (state) => state.notificationsUnread > 0,
  },

  actions: {
    setAuth(user, token) {
      this.user = user
      this.token = token
      this.sessionType = hasAdminAccess(user) ? 'admin' : 'user'
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
        this.summary = res.data.summary || {}
        this.onboarding = res.data.onboarding || null
        this.currentTask = res.data.current_task || null
        this.notifications = res.data.notifications_preview || []
        this.notificationsUnread = this.notifications.filter((item) => !item.read_at).length
        this.sessionType = hasAdminAccess(this.user) ? 'admin' : 'user'
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

    async fetchNotifications(limit = 20) {
      if (!this.token) return

      const { data } = await api.get('/profile/notifications', { params: { limit } })
      this.notifications = data.items || []
      this.notificationsUnread = data.unread_count || 0
    },

    async markNotificationRead(notificationId) {
      if (!this.token) return

      await api.patch(`/profile/notifications/${notificationId}/read`)
      await this.fetchNotifications(20)
    },

    async syncOnboarding(payload) {
      if (!this.token) return

      const { data } = await api.patch('/profile/onboarding', payload)
      this.onboarding = data
    },

    logout() {
      this.user = null
      this.children = []
      this.stats = {}
      this.summary = {}
      this.onboarding = null
      this.currentTask = null
      this.notifications = []
      this.notificationsUnread = 0
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

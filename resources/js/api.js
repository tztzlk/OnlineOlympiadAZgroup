import axios from 'axios'
import { useUserStore } from '../stores/user'

const baseURL = import.meta.env.VITE_API_URL || '/api'

const api = axios.create({
  baseURL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

api.interceptors.request.use(
  (config) => {
    try {
      const userStore = useUserStore()

      if (userStore?.token) {
        config.headers.Authorization = `Bearer ${userStore.token}`
      }
    } catch (e) {
      console.warn('Auth interceptor error', e)
    }

    return config
  },
  (error) => Promise.reject(error)
)

api.interceptors.response.use(
  (response) => response,
  (error) => {
    // 401 на форме входа — это «неверный пароль», а не истёкшая сессия: его показывает сама форма.
    const isAuthAttempt = /\/auth\/(admin\/)?login$/.test(error.config?.url || '')

    if (error.response?.status === 401 && !isAuthAttempt) {
      let hadSession = false

      try {
        const userStore = useUserStore()
        hadSession = !!userStore.token
        userStore.logout?.()
      } catch {}

      const isAdminPath = window.location.pathname.startsWith('/admin')
      const target = isAdminPath ? '/admin-login' : '/login'

      if (hadSession && window.location.pathname !== target) {
        window.location.href = target
      }
    }

    return Promise.reject(error)
  }
)

export default api
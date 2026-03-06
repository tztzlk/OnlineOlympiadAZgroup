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
    if (error.response?.status === 401) {
      try {
        const userStore = useUserStore()
        userStore.logout?.()
      } catch {}

      window.location.href = '/login'
    }

    return Promise.reject(error)
  }
)

export default api
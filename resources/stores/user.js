import { defineStore } from 'pinia'
import api from '../js/api'

export const useUserStore = defineStore('user', {

 state: () => ({
   user: null,
   token: localStorage.getItem('token') || null,
   loading: false
 }),

 getters: {
   isAuthenticated: (state) => !!state.token
 },

 actions: {

   setAuth(user, token) {
     this.user = user
     this.token = token
     localStorage.setItem('token', token)
   },

   async fetchUser() {

     if (!this.token || this.loading) return

     try {
       this.loading = true

       const res = await api.get('/profile')

       this.user = res.data.user || res.data

     } catch (error) {

       if (error.response?.status === 401) {
         this.logout()
       }

     } finally {
       this.loading = false
     }
   },

   logout() {
     this.user = null
     this.token = null
     localStorage.removeItem('token')
   }

 }

})
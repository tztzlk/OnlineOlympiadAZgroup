import { createApp } from 'vue'
import App from './App.vue'
import router from '../router/index.js'
import { createPinia } from 'pinia'
import { useUserStore } from '../stores/user.js'
import { i18n } from '../i18n.js'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(i18n)

const userStore = useUserStore()

if (userStore.token) {
  userStore.fetchUser()

}

app.mount('#app')
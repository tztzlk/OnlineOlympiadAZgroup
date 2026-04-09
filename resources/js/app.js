import '../css/app.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from '../router/index.js'
import { createPinia } from 'pinia'
import { useUserStore } from '../stores/user.js'
import { i18n } from '../i18n.js'
import { applySeo, getStaticSeoForPath } from './composables/useSeo.js'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(i18n)

const userStore = useUserStore()

if (userStore.token) {
  userStore.fetchUser()

}

router.afterEach((to) => {
  if (to.path.startsWith('/subjects/')) {
    return
  }

  const seo = getStaticSeoForPath(to.path)
  if (seo) {
    applySeo(seo)
  }
})

app.mount('#app')

document.documentElement.classList.add('app-mounted')

const initialSeo = getStaticSeoForPath(window.location.pathname)
if (initialSeo) {
  applySeo(initialSeo)
}

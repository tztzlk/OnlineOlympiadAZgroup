<template>
  <div class="edit-page">
    <div class="edit-wrap">
      <RouterLink to="/profile" class="back-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        Кабинет
      </RouterLink>

      <div class="edit-card">
        <header class="head">
          <span class="head__icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <div>
            <p class="eyebrow">Профиль</p>
            <h1>Обновите данные аккаунта</h1>
            <p class="head__copy">Поддерживайте контакты и информацию о школе в актуальном состоянии, чтобы команда могла быстро связаться с вами.</p>
          </div>
        </header>

        <div v-if="loadingProfile" class="form-skeleton" aria-busy="true">
          <span v-for="n in 5" :key="n" class="ds-skeleton"></span>
        </div>

        <form v-else @submit.prevent="updateProfile" class="form">
          <label class="ds-field wide">
            <span class="ds-label">Имя и фамилия</span>
            <input v-model="form.name" class="ds-input" type="text" required placeholder="Имя и фамилия" autocomplete="name" />
          </label>
          <label class="ds-field wide">
            <span class="ds-label">Email</span>
            <input v-model="form.email" class="ds-input" type="email" inputmode="email" required placeholder="Email" autocomplete="email" />
          </label>
          <label class="ds-field">
            <span class="ds-label">Телефон</span>
            <input v-model="form.phone" class="ds-input" type="tel" inputmode="tel" required placeholder="Номер телефона" autocomplete="tel" />
          </label>
          <label class="ds-field">
            <span class="ds-label">Город</span>
            <input v-model="form.city" class="ds-input" type="text" required placeholder="Город" autocomplete="address-level2" />
          </label>
          <label class="ds-field wide">
            <span class="ds-label">Школа</span>
            <input v-model="form.school" class="ds-input" type="text" required placeholder="Школа" autocomplete="organization" />
          </label>

          <p v-if="message" class="ds-msg success wide" role="status">{{ message }}</p>
          <p v-if="error" class="ds-msg error wide" role="alert">{{ error }}</p>

          <div class="buttons wide">
            <button type="button" class="ds-btn ds-btn-ghost ds-btn-lg" @click="router.back()">Отмена</button>
            <button type="submit" class="ds-btn ds-btn-primary ds-btn-lg" :disabled="loading">
              <span v-if="loading" class="ds-spinner" aria-hidden="true"></span>
              {{ loading ? 'Сохранение...' : 'Сохранить' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { useUserStore } from '../stores/user'

const router = useRouter()
const userStore = useUserStore()

const form = ref({
  name: '',
  email: '',
  school: '',
  city: '',
  phone: '',
})

const loading = ref(false)
const loadingProfile = ref(true)
const message = ref('')
const error = ref('')

onMounted(async () => {
  try {
    const res = await api.get('/profile')
    // /profile отдаёт { user, children, ... } — данные аккаунта лежат в user.
    const user = res.data.user || res.data
    form.value = {
      name: user.name || '',
      email: user.email || '',
      school: user.school || '',
      city: user.city || '',
      phone: user.phone || '',
    }
  } catch {
    router.push('/login')
  } finally {
    loadingProfile.value = false
  }
})

const updateProfile = async () => {
  loading.value = true
  error.value = ''
  message.value = ''

  try {
    const { data } = await api.put('/profile', form.value)
    if (data.user) {
      userStore.user = data.user
      localStorage.setItem('user', JSON.stringify(data.user))
    }
    message.value = 'Профиль успешно обновлён.'
    setTimeout(() => router.push('/profile'), 900)
  } catch (err) {
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)[0][0]
    } else {
      error.value = 'Ошибка обновления.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.edit-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 28px) 20px 72px;
  background:
    radial-gradient(700px 360px at 100% 0%, color-mix(in srgb, var(--brand) 9%, transparent), transparent 70%),
    var(--bg);
}

.edit-wrap {
  max-width: 680px;
  margin: 0 auto;
  display: grid;
  gap: 14px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  justify-self: start;
  color: var(--text-secondary);
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
}

.back-link:hover { color: var(--brand-ink); }

.edit-card {
  padding: clamp(22px, 4vw, 36px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-md);
}

.head {
  display: flex;
  gap: 16px;
  margin-bottom: 26px;
}

.head__icon {
  width: 56px;
  height: 56px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 18px;
  background: var(--brand);
  color: var(--sun);
  transform: rotate(-5deg);
}

.eyebrow {
  margin-bottom: 4px;
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

h1 { font-size: clamp(22px, 3vw, 30px); }

.head__copy {
  margin-top: 8px;
  color: var(--text-secondary);
  font-size: 15.5px;
}

.form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.wide { grid-column: 1 / -1; }

.form-skeleton {
  display: grid;
  gap: 14px;
}

.form-skeleton .ds-skeleton { height: 50px; }

.buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 6px;
}

@media (max-width: 560px) {
  .edit-page { padding: calc(var(--header-h) + 16px) 12px 96px; }
  .head { flex-direction: column; }
  .form { grid-template-columns: 1fr; }
  .buttons { flex-direction: column-reverse; }
  .buttons .ds-btn { width: 100%; }
}
</style>

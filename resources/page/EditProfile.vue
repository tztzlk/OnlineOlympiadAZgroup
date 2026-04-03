<template>
  <div class="edit-page">
    <div class="edit-card">
      <div class="head">
        <p class="eyebrow">Профиль</p>
        <h1>Обновите данные аккаунта</h1>
        <p>Поддерживайте контакты и информацию о школе в актуальном состоянии, чтобы команда могла быстро связаться с вами.</p>
      </div>

      <form @submit.prevent="updateProfile" class="form">
        <label class="field">
          <span>Имя</span>
          <input v-model="form.name" type="text" required placeholder="Имя" aria-label="Имя" />
        </label>
        <label class="field">
          <span>Email</span>
          <input v-model="form.email" type="email" required placeholder="Email" aria-label="Email" />
        </label>
        <label class="field">
          <span>Школа</span>
          <input v-model="form.school" type="text" required placeholder="Школа" aria-label="Школа" />
        </label>
        <label class="field">
          <span>Город</span>
          <input v-model="form.city" type="text" required placeholder="Город" aria-label="Город" />
        </label>
        <label class="field">
          <span>Телефон</span>
          <input v-model="form.phone" type="tel" required placeholder="Номер телефона" aria-label="Номер телефона" />
        </label>

        <p v-if="message" class="message success">{{ message }}</p>
        <p v-if="error" class="message error">{{ error }}</p>

        <div class="buttons">
          <button type="submit" class="save-btn" :disabled="loading">
            {{ loading ? 'Сохранение...' : 'Сохранить' }}
          </button>
          <button type="button" class="cancel-btn" @click="router.back()">Отмена</button>
        </div>
      </form>
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
const message = ref('')
const error = ref('')

onMounted(async () => {
  try {
    const res = await api.get('/profile')
    form.value = {
      name: res.data.name || '',
      email: res.data.email || '',
      school: res.data.school || '',
      city: res.data.city || '',
      phone: res.data.phone || '',
    }
  } catch {
    router.push('/login')
  }
})

const updateProfile = async () => {
  loading.value = true
  error.value = ''
  message.value = ''

  try {
    const { data } = await api.put('/profile', form.value)
    userStore.user = data.user
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
.edit-page { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; background: var(--bg); }
.edit-card { width: 100%; max-width: 560px; background: var(--surface); padding: 34px; border-radius: var(--radius-lg); border: 1px solid var(--surface-border); box-shadow: var(--shadow-card); }
.head { display: grid; gap: 10px; margin-bottom: 20px; }
.eyebrow { margin: 0; text-transform: uppercase; letter-spacing: .08em; color: var(--accent-strong); font-size: 12px; font-weight: 700; }
h1 { margin: 0; color: var(--text); }
.head p:last-child { color: var(--text-secondary); }
.form { display: grid; gap: 14px; }
.field { display: grid; gap: 8px; font-size: 14px; font-weight: 600; }
input { width: 100%; padding: 14px 16px; border-radius: var(--radius-sm); border: 1px solid var(--surface-border); background: rgba(255,252,245,.95); color: var(--text); box-sizing: border-box; }
input::placeholder { color: #9ca3af; }
.buttons { display: flex; gap: 12px; margin-top: 8px; flex-wrap: wrap; }
.save-btn, .cancel-btn { flex: 1 1 0; min-width: 0; padding: 14px 20px; border: none; border-radius: var(--radius-sm); font-size: 1rem; font-weight: 700; cursor: pointer; }
.save-btn { background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%); color: var(--text); box-shadow: 0 12px 26px rgba(201,171,99,.2); }
.save-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.cancel-btn { background: rgba(79,167,116,.1); color: #316a49; }
.message { margin: 0; font-size: .95rem; border-radius: var(--radius-sm); padding: 12px 14px; }
.message.success { color: #2f6f4b; background: var(--success-bg); }
.message.error { color: #8f3b3b; background: var(--danger-bg); }
@media (max-width: 480px) { .edit-card { padding: 24px 18px; } }
</style>

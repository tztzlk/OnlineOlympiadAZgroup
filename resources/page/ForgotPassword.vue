<template>
  <div class="auth-page">
    <div class="auth-card">
      <p class="eyebrow">Password Recovery</p>
      <h1>Восстановление пароля</h1>
      <p class="subtext">Введите email, и мы отправим ссылку для сброса пароля.</p>

      <form @submit.prevent="submit" class="auth-form">
        <input v-model="email" type="email" placeholder="you@example.com" required />
        <p v-if="message" class="message success">{{ message }}</p>
        <p v-if="error" class="message error">{{ error }}</p>
        <button class="submit-btn" :disabled="loading">
          {{ loading ? 'Отправляем...' : 'Отправить ссылку' }}
        </button>
      </form>

      <div class="links">
        <RouterLink to="/login">Вернуться ко входу</RouterLink>
        <RouterLink to="/help-desk">Нужна помощь?</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../js/api'

const email = ref('')
const message = ref('')
const error = ref('')
const loading = ref(false)

const submit = async () => {
  loading.value = true
  message.value = ''
  error.value = ''

  try {
    const { data } = await api.post('/auth/forgot-password', { email: email.value.trim() })
    message.value = data.message
  } catch (err) {
    error.value = err.response?.data?.message || 'Не удалось отправить ссылку.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-page { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--bg); }
.auth-card { width: min(480px, 100%); padding: 32px; border-radius: 28px; background: var(--surface); border: 1px solid var(--surface-border); }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: .08em; color: #e11d48; font-size: 12px; font-weight: 700; }
h1 { margin: 0; color: var(--text-on-surface); }
.subtext { color: var(--text-muted-on-surface); line-height: 1.6; margin: 12px 0 24px; }
.auth-form { display: grid; gap: 14px; }
input { width: 100%; padding: 14px 16px; border-radius: 14px; border: 1px solid var(--surface-border); background: var(--surface-soft); color: var(--text-on-surface); }
.message { margin: 0; font-size: 14px; }
.success { color: #16a34a; }
.error { color: #dc2626; }
.submit-btn { border: 0; border-radius: 14px; padding: 14px 18px; background: linear-gradient(90deg, #e11d48, #fb7185); color: #fff; font-weight: 700; cursor: pointer; }
.links { display: flex; justify-content: space-between; gap: 12px; margin-top: 18px; }
.links a { color: #e11d48; text-decoration: none; font-weight: 600; }
@media (max-width: 560px) { .auth-card { padding: 24px 18px; } .links { flex-direction: column; } }
</style>

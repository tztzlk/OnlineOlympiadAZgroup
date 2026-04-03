<template>
  <div class="auth-page">
    <div class="auth-card">
      <p class="eyebrow">Восстановление</p>
      <h1>Сбросьте пароль через email</h1>
      <p class="subtext">Введите адрес электронной почты, и мы отправим ссылку для восстановления доступа к кабинету.</p>

      <form @submit.prevent="submit" class="auth-form">
        <label class="field">
          <span>Email</span>
          <input v-model="email" type="email" placeholder="you@example.com" required />
        </label>
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
.auth-card { width: min(500px, 100%); padding: 32px; border-radius: var(--radius-lg); background: var(--surface); border: 1px solid var(--surface-border); box-shadow: var(--shadow-card); }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: .08em; color: var(--accent-strong); font-size: 12px; font-weight: 700; }
h1 { margin: 0; color: var(--text-on-surface); }
.subtext { color: var(--text-muted-on-surface); line-height: 1.6; margin: 12px 0 24px; }
.auth-form { display: grid; gap: 14px; }
.field { display: grid; gap: 8px; font-size: 14px; font-weight: 600; }
input { width: 100%; padding: 14px 16px; border-radius: var(--radius-sm); border: 1px solid var(--surface-border); background: rgba(255,252,245,.95); color: var(--text-on-surface); }
.message { margin: 0; font-size: 14px; border-radius: var(--radius-sm); padding: 12px 14px; }
.success { color: #2f6f4b; background: var(--success-bg); }
.error { color: #8f3b3b; background: var(--danger-bg); }
.submit-btn { border: 0; border-radius: var(--radius-sm); padding: 14px 18px; background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%); color: var(--text); font-weight: 700; cursor: pointer; box-shadow: 0 12px 26px rgba(201,171,99,.2); }
.links { display: flex; justify-content: space-between; gap: 12px; margin-top: 18px; }
.links a { color: var(--accent-strong); text-decoration: none; font-weight: 700; }
@media (max-width: 560px) { .auth-card { padding: 24px 18px; } .links { flex-direction: column; } }
</style>

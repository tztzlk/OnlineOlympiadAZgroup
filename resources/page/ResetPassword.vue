<template>
  <div class="auth-page">
    <div class="auth-card">
      <p class="eyebrow">Reset Password</p>
      <h1>Новый пароль</h1>
      <p class="subtext">Укажите новый пароль для аккаунта {{ email || 'пользователя' }}.</p>

      <form @submit.prevent="submit" class="auth-form">
        <input v-model="password" type="password" placeholder="Новый пароль" required />
        <input v-model="passwordConfirmation" type="password" placeholder="Повторите пароль" required />
        <p v-if="message" class="message success">{{ message }}</p>
        <p v-if="error" class="message error">{{ error }}</p>
        <button class="submit-btn" :disabled="loading">
          {{ loading ? 'Сохраняем...' : 'Обновить пароль' }}
        </button>
      </form>

      <div class="links">
        <RouterLink to="/login">Перейти ко входу</RouterLink>
        <RouterLink to="/help-desk">Help Desk</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../js/api'

const route = useRoute()
const router = useRouter()

const password = ref('')
const passwordConfirmation = ref('')
const message = ref('')
const error = ref('')
const loading = ref(false)

const token = computed(() => route.query.token || '')
const email = computed(() => route.query.email || '')

const submit = async () => {
  loading.value = true
  message.value = ''
  error.value = ''

  try {
    const { data } = await api.post('/auth/reset-password', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    message.value = data.message
    setTimeout(() => router.push('/login'), 1200)
  } catch (err) {
    error.value = err.response?.data?.message || 'Не удалось обновить пароль.'
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

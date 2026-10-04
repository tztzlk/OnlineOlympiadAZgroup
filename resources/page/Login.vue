<template>
  <AuthShell>
    <div>
      <header class="auth-head">
        <p class="auth-eyebrow">Вход</p>
        <h1>Войдите в аккаунт</h1>
        <p class="auth-sub">Введите email и пароль, чтобы открыть кабинет.</p>
      </header>

      <form @submit.prevent="handleLogin" class="auth-form" novalidate>
        <label class="ds-field">
          <span>Email</span>
          <input
            v-model="email"
            class="ds-input"
            :class="{ 'is-error': !!error }"
            type="email"
            inputmode="email"
            placeholder="you@example.com"
            autocomplete="email"
            required
          />
        </label>

        <label class="ds-field">
          <span>Пароль</span>
          <div class="password-wrap">
            <input
              v-model="password"
              class="ds-input"
              :class="{ 'is-error': !!error }"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Введите пароль"
              autocomplete="current-password"
              required
            />
            <button
              type="button"
              class="password-toggle"
              :aria-label="showPassword ? 'Скрыть' : 'Показать'"
              :aria-pressed="showPassword ? 'true' : 'false'"
              @click="showPassword = !showPassword"
            >
              <svg v-if="showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.9 17.9A10.1 10.1 0 0 1 12 20c-7 0-11-8-11-8a18.5 18.5 0 0 1 5.1-5.9M9.9 4.2A9.1 9.1 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.2 3.2M14.1 14.1a3 3 0 1 1-4.2-4.2M1 1l22 22"/></svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </label>

        <div class="auth-links">
          <RouterLink to="/forgot-password">Забыли пароль?</RouterLink>
          <RouterLink to="/help-desk">Нужна помощь?</RouterLink>
        </div>

        <p v-if="error" class="ds-msg error" role="alert">{{ error }}</p>

        <button type="submit" class="ds-btn ds-btn-primary ds-btn-lg ds-btn-block" :disabled="loading">
          <span v-if="loading" class="ds-spinner" aria-hidden="true"></span>
          {{ loading ? 'Входим...' : 'Войти в аккаунт' }}
        </button>
      </form>

      <div class="auth-foot">
        <p>Нет аккаунта? <RouterLink to="/register">Зарегистрироваться</RouterLink></p>
      </div>
    </div>
  </AuthShell>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { useUserStore } from '../stores/user'
import AuthShell from '../components/AuthShell.vue'
import '../css/auth.css'

const router = useRouter()
const userStore = useUserStore()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    const response = await api.post('/auth/login', {
      email: email.value,
      password: password.value,
    })

    userStore.setAuth(response.data.user, response.data.token)
    localStorage.setItem('session_type', 'user')
    router.push('/profile')
  } catch (err) {
    if (err.response?.status === 401) {
      error.value = 'Неверный email или пароль.'
    } else if (err.response?.status === 429) {
      error.value = 'Слишком много попыток входа. Подождите несколько минут и попробуйте снова.'
    } else if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)[0][0]
    } else {
      error.value = err.response?.data?.message || 'Не удалось выполнить вход. Попробуйте ещё раз.'
    }
  } finally {
    loading.value = false
  }
}
</script>

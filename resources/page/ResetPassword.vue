<template>
  <AuthShell>
    <div>
      <header class="auth-head">
        <p class="auth-eyebrow">Новый пароль</p>
        <h1>Обновите пароль от аккаунта</h1>
        <p class="auth-sub">Укажите новый пароль для аккаунта {{ email || 'пользователя' }} и затем вернитесь ко входу.</p>
      </header>

      <form @submit.prevent="submit" class="auth-form">
        <label class="ds-field">
          <span>Новый пароль</span>
          <input v-model="password" class="ds-input" type="password" autocomplete="new-password" placeholder="Новый пароль" required />
        </label>
        <label class="ds-field">
          <span>Повторите пароль</span>
          <input v-model="passwordConfirmation" class="ds-input" :class="{ 'is-error': mismatch }" type="password" autocomplete="new-password" placeholder="Повторите пароль" required />
        </label>

        <ul class="password-rules" aria-label="Требования к паролю">
          <li :class="{ 'is-ok': checks.length }">Минимум 12 символов</li>
          <li :class="{ 'is-ok': checks.mixedCase }">Заглавные и строчные буквы</li>
          <li :class="{ 'is-ok': checks.number }">Хотя бы одна цифра</li>
          <li :class="{ 'is-ok': checks.symbol }">Хотя бы один спецсимвол</li>
        </ul>

        <p v-if="message" class="ds-msg success" role="status">{{ message }}</p>
        <p v-if="error" class="ds-msg error" role="alert">{{ error }}</p>

        <button class="ds-btn ds-btn-primary ds-btn-lg ds-btn-block" :disabled="loading">
          <span v-if="loading" class="ds-spinner" aria-hidden="true"></span>
          {{ loading ? 'Сохраняем...' : 'Обновить пароль' }}
        </button>
      </form>

      <div class="auth-foot auth-links">
        <RouterLink to="/login">Перейти ко входу</RouterLink>
        <RouterLink to="/help-desk">Нужна помощь?</RouterLink>
      </div>
    </div>
  </AuthShell>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../js/api'
import AuthShell from '../components/AuthShell.vue'
import '../css/auth.css'

const route = useRoute()
const router = useRouter()

const password = ref('')
const passwordConfirmation = ref('')
const message = ref('')
const error = ref('')
const loading = ref(false)

const token = computed(() => route.query.token || '')
const email = computed(() => route.query.email || '')

const checks = computed(() => ({
  length: password.value.length >= 12,
  mixedCase: /[A-ZА-Я]/.test(password.value) && /[a-zа-я]/.test(password.value),
  number: /[0-9]/.test(password.value),
  symbol: /[^A-Za-zА-Яа-я0-9]/.test(password.value),
}))

const mismatch = computed(() => !!passwordConfirmation.value && passwordConfirmation.value !== password.value)

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
    const errors = err.response?.data?.errors
    error.value = errors ? Object.values(errors)[0][0] : (err.response?.data?.message || 'Не удалось обновить пароль.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.password-rules {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 6px 16px;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  background: var(--bg);
  list-style: none;
}

.password-rules li {
  position: relative;
  padding-left: 24px;
  color: var(--text-secondary);
  font-size: 13.5px;
}

.password-rules li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid var(--border-strong);
  transform: translateY(-50%);
}

.password-rules li.is-ok { color: var(--success-ink); }

.password-rules li.is-ok::before {
  border-color: var(--success);
  background: var(--success) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E") center / 10px no-repeat;
}

@media (max-width: 480px) {
  .password-rules { grid-template-columns: 1fr; }
}
</style>

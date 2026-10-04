<template>
  <div class="admin-login">
    <div class="card">
      <div class="card__header">
        <div class="logo" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <h1 class="card__title">Панель управления</h1>
        <p class="card__subtitle">Войдите, чтобы продолжить</p>
      </div>

      <form class="form" @submit.prevent="login">
        <label class="field">
          <span class="field__label">Электронная почта</span>
          <span class="field__wrapper">
            <span class="field__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="M22 7l-10 6L2 7"/></svg>
            </span>
            <input
              v-model="email"
              type="email"
              inputmode="email"
              placeholder="admin@example.com"
              required
              autocomplete="email"
            />
          </span>
        </label>

        <label class="field">
          <span class="field__label">Пароль</span>
          <span class="field__wrapper">
            <span class="field__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.6 7.6a5.5 5.5 0 1 1-7.8 7.8 5.5 5.5 0 0 1 7.8-7.8zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
            </span>
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            />
            <button type="button" class="field__toggle" :aria-label="showPassword ? 'Скрыть пароль' : 'Показать пароль'" @click="showPassword = !showPassword">
              <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.9 17.9A10.1 10.1 0 0 1 12 20c-7 0-11-8-11-8a18.5 18.5 0 0 1 5.1-5.9M9.9 4.2A9.1 9.1 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.2 3.2M14.1 14.1a3 3 0 1 1-4.2-4.2M1 1l22 22"/></svg>
            </button>
          </span>
        </label>

        <Transition name="error">
          <div v-if="error" class="error-banner" role="alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            {{ error }}
          </div>
        </Transition>

        <button type="submit" class="submit-btn" :disabled="loading">
          <span v-if="!loading">Войти</span>
          <span v-else class="loader" aria-label="Входим"></span>
        </button>
      </form>

      <p class="card__footer">Только для авторизованных сотрудников</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../../js/api'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { firstAdminRoute, hasAdminAccess } from '../../js/adminAccess'

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)
const router = useRouter()
const userStore = useUserStore()

const login = async () => {
  loading.value = true
  error.value = ''

  try {
    const res = await api.post('/auth/admin/login', {
      email: email.value,
      password: password.value,
    })

    const user = res.data.user

    if (!hasAdminAccess(user)) {
      throw new Error('Доступ только для администратора')
    }

    const token = res.data.token
    localStorage.setItem('token', token)
    localStorage.setItem('user', JSON.stringify(user))
    localStorage.setItem('session_type', 'admin')
    userStore.setAuth(user, token)

    await router.replace(firstAdminRoute(user))
  } catch (err) {
    error.value = err.response?.status === 429
      ? 'Слишком много попыток входа. Подождите несколько минут.'
      : (err.response?.data?.message || err.message || 'Ошибка входа')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.admin-login {
  min-height: 100dvh;
  display: grid;
  place-items: center;
  padding: 24px 16px;
  background:
    radial-gradient(600px 400px at 20% 10%, rgba(43, 91, 245, 0.35), transparent 70%),
    radial-gradient(500px 360px at 90% 90%, rgba(255, 201, 51, 0.12), transparent 70%),
    #0d1328;
}

.card {
  width: min(420px, 100%);
  display: grid;
  gap: 24px;
  padding: 36px 32px 28px;
  border-radius: var(--radius-xl);
  background: #ffffff;
  box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45);
  color: #111a33;
}

.card__header {
  display: grid;
  justify-items: center;
  gap: 8px;
  text-align: center;
}

.logo {
  width: 56px;
  height: 56px;
  display: grid;
  place-items: center;
  margin-bottom: 6px;
  border-radius: 18px;
  background: #2b5bf5;
  color: #ffc933;
  box-shadow: 0 10px 24px rgba(43, 91, 245, 0.35);
}

.card__title {
  font-size: 26px;
  color: #111a33;
}

.card__subtitle {
  color: #5b6585;
  font-size: 15px;
}

.form {
  display: grid;
  gap: 16px;
}

.field {
  display: grid;
  gap: 6px;
}

.field__label {
  color: #111a33;
  font-size: 14px;
  font-weight: 600;
}

.field__wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.field__icon {
  position: absolute;
  left: 14px;
  display: grid;
  color: #8a93b0;
  pointer-events: none;
}

.field input {
  width: 100%;
  min-height: 50px;
  padding: 12px 48px 12px 44px;
  border-radius: var(--radius-sm);
  border: 1.5px solid rgba(17, 26, 51, 0.16);
  background: #ffffff;
  color: #111a33;
  font-size: 16px;
}

.field input:focus {
  outline: none;
  border-color: #2b5bf5;
  box-shadow: 0 0 0 3px rgba(43, 91, 245, 0.25);
}

.field__toggle {
  position: absolute;
  right: 6px;
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  border: 0;
  border-radius: 10px;
  background: transparent;
  color: #8a93b0;
}

.field__toggle:hover { color: #2b5bf5; background: #f3f6ff; }

.error-banner {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 11px 14px;
  border-radius: var(--radius-sm);
  background: #fdeaea;
  color: #a8242a;
  font-size: 14px;
  font-weight: 500;
}

.submit-btn {
  display: grid;
  place-items: center;
  min-height: 52px;
  border: 0;
  border-radius: var(--radius-sm);
  background: #2b5bf5;
  color: #ffffff;
  font-size: 16px;
  font-weight: 700;
  box-shadow: 0 8px 20px rgba(43, 91, 245, 0.3);
}

.submit-btn:hover:not(:disabled) { background: #1f49d6; }
.submit-btn:disabled { opacity: 0.75; cursor: wait; }

.loader {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 2.5px solid rgba(255, 255, 255, 0.35);
  border-top-color: #ffffff;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.card__footer {
  color: #8a93b0;
  font-size: 13px;
  text-align: center;
}

.error-enter-active,
.error-leave-active { transition: opacity var(--dur) ease, transform var(--dur) var(--ease-out); }
.error-enter-from,
.error-leave-to { opacity: 0; transform: translateY(-4px); }

@media (max-width: 480px) {
  .card { padding: 28px 20px 22px; }
}
</style>

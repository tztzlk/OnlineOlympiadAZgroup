<template>
  <div class="auth-page">
    <div class="auth-shell">
      <section class="auth-side">
        <p class="eyebrow">Eurica</p>
        <h1>Войдите в личный кабинет</h1>
        <p class="lead">
          Все заявки, оплаты, результаты и сертификаты собраны в одном месте.
        </p>
      </section>

      <section class="auth-card">
        <div class="form-header">
          <p class="eyebrow">Вход</p>
          <h2>Войдите в аккаунт</h2>
          <p>Введите email и пароль, чтобы открыть кабинет.</p>
        </div>

        <form @submit.prevent="handleLogin" class="auth-form" novalidate>
          <label class="field">
            <span>Email</span>
            <input
              v-model="email"
              type="email"
              placeholder="you@example.com"
              autocomplete="email"
              required
            />
          </label>

          <label class="field">
            <span>Пароль</span>
            <div class="password-wrap">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Введите пароль"
                autocomplete="current-password"
                required
              />
              <button type="button" class="toggle-btn" @click="showPassword = !showPassword">
                {{ showPassword ? 'Скрыть' : 'Показать' }}
              </button>
            </div>
          </label>

          <div class="helper-row">
            <RouterLink to="/forgot-password">Забыли пароль?</RouterLink>
            <RouterLink to="/help-desk">Нужна помощь?</RouterLink>
          </div>

          <div v-if="error" class="message error">{{ error }}</div>

          <button type="submit" class="submit-btn" :disabled="loading">
            {{ loading ? 'Входим...' : 'Войти в аккаунт' }}
          </button>
        </form>

        <div class="form-footer">
          <p>Нет аккаунта? <RouterLink to="/register">Зарегистрироваться</RouterLink></p>
          <p>Для сотрудников: <RouterLink to="/admin-login">перейти в админ-панель</RouterLink></p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { useUserStore } from '../stores/user'

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

<style scoped>
* { box-sizing: border-box; }

.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 20px;
  padding-top: 100px;
  background: var(--bg);
}

.auth-shell {
  width: min(1120px, 100%);
  display: grid;
  grid-template-columns: 1.15fr 0.95fr;
  gap: 24px;
  align-items: stretch;
}

.auth-side,
.auth-card {
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-soft);
}

.auth-side {
  padding: 40px;
  display: grid;
  gap: 12px;
  align-content: center;
  background: var(--card);
}

.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--green);
}

.lead {
  max-width: 56ch;
  color: var(--text-secondary);
  font-size: 17px;
}

.auth-card {
  padding: 34px;
  display: grid;
  align-content: center;
  gap: 22px;
}

.form-header {
  display: grid;
  gap: 10px;
}

.form-header p:last-child {
  color: var(--text-secondary);
}

.auth-form {
  display: grid;
  gap: 16px;
}

.field {
  display: grid;
  gap: 8px;
  color: var(--text);
  font-size: 14px;
  font-weight: 600;
}

.field input {
  width: 100%;
  min-height: 54px;
  border-radius: var(--radius-sm);
  border: 1.5px solid var(--border);
  background: var(--card);
  padding: 14px 16px;
  color: var(--text);
  font-family: 'Inter', sans-serif;
  font-size: 15px;
}
.field input:focus {
  outline: none;
  border-color: rgba(22, 163, 74, 0.34);
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.08);
}

.password-wrap {
  position: relative;
}

.password-wrap input {
  padding-right: 100px;
}

.toggle-btn {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  border: 0;
  background: transparent;
  color: var(--text-secondary);
  font-size: 13px;
  cursor: pointer;
}

.helper-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  font-size: 14px;
}

.helper-row a,
.form-footer a {
  color: var(--green);
  font-weight: 700;
  text-decoration: none;
}

.message {
  border-radius: var(--radius-sm);
  padding: 13px 14px;
  font-size: 14px;
}

.message.error {
  background: var(--danger-bg);
  color: #b91c1c;
  border: 1px solid rgba(220, 38, 38, 0.15);
}

.submit-btn {
  min-height: 54px;
  border: none;
  border-radius: var(--radius-sm);
  background: var(--green);
  color: #ffffff;
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.28);
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.submit-btn:hover:not(:disabled) {
  background: var(--green-hover);
  transform: translateY(-2px);
  box-shadow: 0 14px 32px rgba(22, 163, 74, 0.34);
}

.submit-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
}

.form-footer {
  display: grid;
  gap: 10px;
  color: var(--text-secondary);
  font-size: 14px;
}

@media (max-width: 900px) {
  .auth-shell {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .auth-page {
    padding: 20px 14px;
  }

  .auth-side,
  .auth-card {
    padding: 24px 20px;
  }
}
</style>

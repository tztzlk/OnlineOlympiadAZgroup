<template>
  <div class="auth-page">
    <div class="auth-shell">
      <section class="auth-side">
        <p class="eyebrow">Eurica</p>
        <h1>Войдите в личный кабинет</h1>
        <p class="lead">
          Все заявки, оплаты, результаты и сертификаты собраны в одном месте.
        </p>

        <div class="side-chips">
          <span class="side-chip">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            1 247+ участников
          </span>
          <span class="side-chip">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Мгновенный результат
          </span>
          <span class="side-chip">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Сертификат участника
          </span>
        </div>

        <div class="side-stats">
          <div class="side-stat">
            <strong>97.4%</strong>
            <span>рекомендуют</span>
          </div>
          <div class="side-stat-div"></div>
          <div class="side-stat">
            <strong>8 мин</strong>
            <span>до результата</span>
          </div>
          <div class="side-stat-div"></div>
          <div class="side-stat">
            <strong>3 предмета</strong>
            <span>на выбор</span>
          </div>
        </div>
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
  min-height: 100dvh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 20px;
  padding-top: 100px;
  background: var(--bg);
}

.auth-shell {
  width: min(1100px, 100%);
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 20px;
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
  padding: 44px 40px;
  display: grid;
  gap: 20px;
  align-content: center;
  background: var(--card);
}

.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 11px;
  font-weight: 800;
  color: var(--green);
}

.lead {
  max-width: 52ch;
  color: var(--text-secondary);
  font-size: 16px;
  line-height: 1.65;
  margin: 0;
}

.side-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.side-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 13px;
  border-radius: 999px;
  background: var(--bg-alt);
  border: 1.5px solid var(--border);
  font-size: 12px;
  font-weight: 500;
  color: var(--text-secondary);
}
.side-chip svg { color: var(--green); flex-shrink: 0; }

.side-stats {
  display: inline-flex;
  align-items: center;
  gap: 18px;
  padding: 14px 22px;
  border-radius: 16px;
  background: var(--bg-alt);
  border: 1.5px solid var(--border);
  width: fit-content;
}

.side-stat {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.side-stat strong {
  font-size: 17px;
  font-weight: 800;
  color: var(--text);
  letter-spacing: -0.02em;
}
.side-stat span {
  font-size: 10px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.07em;
}
.side-stat-div {
  width: 1px;
  height: 28px;
  background: var(--border);
  flex-shrink: 0;
}

.auth-card {
  padding: 34px;
  display: grid;
  align-content: center;
  gap: 22px;
}

.form-header { display: grid; gap: 8px; }
.form-header p:last-child { color: var(--text-secondary); font-size: 14px; margin: 0; }

.auth-form { display: grid; gap: 16px; }

.field {
  display: grid;
  gap: 7px;
  color: var(--text);
  font-size: 13px;
  font-weight: 600;
}

.field input {
  width: 100%;
  min-height: 52px;
  border-radius: 12px;
  border: 1.5px solid var(--border);
  background: var(--card);
  padding: 13px 16px;
  color: var(--text);
  font-size: 15px;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}
.field input:focus {
  outline: none;
  border-color: rgba(22, 163, 74, 0.34);
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.08);
}

.password-wrap { position: relative; }
.password-wrap input { padding-right: 96px; }

.toggle-btn {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  border: 0;
  background: transparent;
  color: var(--text-secondary);
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.helper-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  font-size: 13px;
}
.helper-row a, .form-footer a {
  color: var(--green);
  font-weight: 700;
  text-decoration: none;
}
.helper-row a:hover, .form-footer a:hover { text-decoration: underline; }

.message {
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 14px;
}
.message.error {
  background: var(--danger-bg);
  color: #b91c1c;
  border: 1px solid rgba(220, 38, 38, 0.15);
}

.submit-btn {
  min-height: 52px;
  border: none;
  border-radius: 13px;
  background: var(--green);
  color: #ffffff;
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.28);
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}
.submit-btn:hover:not(:disabled) {
  background: var(--green-hover);
  transform: translateY(-2px);
  box-shadow: 0 14px 32px rgba(22, 163, 74, 0.34);
}
.submit-btn:active:not(:disabled) {
  transform: scale(0.98);
  box-shadow: 0 4px 14px rgba(22, 163, 74, 0.2);
  transition-duration: 0.08s;
}
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.form-footer {
  display: grid;
  gap: 8px;
  color: var(--text-secondary);
  font-size: 13px;
}

@media (max-width: 900px) {
  .auth-shell { grid-template-columns: 1fr; }
  .side-stats { width: 100%; }
}

@media (max-width: 640px) {
  .auth-page { padding: 18px 14px; }
  .auth-side, .auth-card { padding: 22px 18px; }
}
</style>

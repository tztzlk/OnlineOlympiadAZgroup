<template>
  <div class="auth-page">
    <div class="auth-shell">
      <section class="auth-side">
        <p class="eyebrow">Online Olympiad</p>
        <h1>Возвращайтесь в кабинет без лишнего стресса</h1>
        <p class="lead">
          Здесь родители и участники отслеживают заявки, оплаты, тренировки и результаты в одной понятной системе.
        </p>

        <div class="benefit-list">
          <article v-for="item in benefits" :key="item.title" class="benefit-card">
            <strong>{{ item.title }}</strong>
            <span>{{ item.text }}</span>
          </article>
        </div>

        <div class="auth-side-footer">
          <span>Безопасный вход</span>
          <span>Поддержка через Help Desk</span>
          <span>Результаты и сертификаты в одном профиле</span>
        </div>
      </section>

      <section class="auth-card">
        <div class="form-header">
          <p class="eyebrow">Вход</p>
          <h2>Войдите в аккаунт</h2>
          <p>Введите email и пароль, чтобы продолжить работу с профилем и олимпиадами.</p>
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

const benefits = [
  {
    title: 'Понятный личный кабинет',
    text: 'Все заявки, дети, оплаты и результаты собраны в одном месте.',
  },
  {
    title: 'Прозрачный статус участия',
    text: 'Сразу видно, где заявка ожидает проверки, а где уже открыт доступ.',
  },
  {
    title: 'Поддержка без ожидания',
    text: 'Если нужен вход, оплата или помощь с тестом, можно быстро написать в поддержку.',
  },
]

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
    router.push('/')
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
  background:
    radial-gradient(circle at top left, rgba(201, 171, 99, 0.18), transparent 28%),
    radial-gradient(circle at bottom right, rgba(79, 167, 116, 0.14), transparent 24%),
    var(--bg);
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
  gap: 22px;
  background:
    linear-gradient(160deg, rgba(255, 249, 238, 0.95), rgba(243, 229, 191, 0.76)),
    var(--surface);
}

.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

.lead {
  max-width: 56ch;
  color: var(--text-secondary);
  font-size: 17px;
}

.benefit-list {
  display: grid;
  gap: 14px;
}

.benefit-card {
  padding: 18px 20px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(141, 111, 49, 0.14);
  background: rgba(255, 252, 244, 0.76);
  display: grid;
  gap: 6px;
}

.benefit-card strong {
  color: var(--text);
  font-size: 18px;
}

.benefit-card span,
.auth-side-footer {
  color: var(--text-secondary);
}

.auth-side-footer {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 18px;
  font-size: 14px;
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
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 245, 0.95);
  padding: 14px 16px;
  color: var(--text);
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
  color: var(--accent-strong);
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
  color: var(--accent-strong);
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
  color: #8f3b3b;
  border: 1px solid rgba(198, 90, 90, 0.18);
}

.submit-btn {
  min-height: 54px;
  border: 0;
  border-radius: var(--radius-sm);
  background: linear-gradient(135deg, var(--accent) 0%, #e1c16f 100%);
  color: var(--text);
  box-shadow: 0 14px 30px rgba(201, 171, 99, 0.26);
  font-size: 16px;
  cursor: pointer;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  background: linear-gradient(135deg, #d5b56d 0%, #e6c778 100%);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
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

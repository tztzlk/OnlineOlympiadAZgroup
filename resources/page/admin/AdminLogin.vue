<template>
  <div class="admin-login">
    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>
    <div class="bg-orb bg-orb--3"></div>

    <div class="card">
      <div class="card__header">
        <div class="logo">OO</div>
        <h1 class="card__title">Панель управления</h1>
        <p class="card__subtitle">Войдите, чтобы продолжить</p>
      </div>

      <form class="form" @submit.prevent="login">
        <div class="field" :class="{ 'field--focused': focusedField === 'email', 'field--filled': email }">
          <label class="field__label">Электронная почта</label>
          <div class="field__wrapper">
            <span class="field__icon">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M2 4a1 1 0 011-1h10a1 1 0 011 1v8a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" stroke="currentColor" stroke-width="1.2"/>
                <path d="M2 4l6 5 6-5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
              </svg>
            </span>
            <input
              v-model="email"
              type="email"
              placeholder="admin@example.com"
              required
              autocomplete="email"
              @focus="focusedField = 'email'"
              @blur="focusedField = ''"
            />
          </div>
        </div>

        <div class="field" :class="{ 'field--focused': focusedField === 'password', 'field--filled': password }">
          <label class="field__label">Пароль</label>
          <div class="field__wrapper">
            <span class="field__icon">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                <path d="M5 7V5a3 3 0 016 0v2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                <circle cx="8" cy="10.5" r="1" fill="currentColor"/>
              </svg>
            </span>
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              autocomplete="current-password"
              @focus="focusedField = 'password'"
              @blur="focusedField = ''"
            />
            <button type="button" class="field__toggle" @click="showPassword = !showPassword">
              <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.2"/>
                <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.2"/>
              </svg>
              <svg v-else width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M2 2l12 12M6.5 6.6A2 2 0 0010 9.5M4 4.3C2.4 5.4 1 8 1 8s2.5 5 7 5c1.3 0 2.5-.4 3.5-1M7 3.1C7.3 3 7.7 3 8 3c4.5 0 7 5 7 5s-.6 1.3-1.7 2.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
        </div>

        <Transition name="error">
          <div v-if="error" class="error-banner">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.2"/>
              <path d="M7 4v3.5M7 9.5v.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            {{ error }}
          </div>
        </Transition>

        <button type="submit" class="submit-btn" :class="{ 'submit-btn--loading': loading }">
          <span v-if="!loading">Войти</span>
          <span v-else class="loader"></span>
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
const focusedField = ref('')
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
    error.value =
      err.response?.data?.message ||
      err.message ||
      'Ошибка входа'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.admin-login {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(160deg, var(--bg-yellow) 0%, var(--bg-alt) 100%);
  position: relative;
  overflow: hidden;
  padding: 24px;
}

.bg-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
}

.bg-orb--1 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(242, 201, 76, 0.28) 0%, transparent 70%);
  top: -100px;
  right: -100px;
  opacity: 0.8;
}

.bg-orb--2 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(58, 158, 58, 0.14) 0%, transparent 70%);
  bottom: -80px;
  left: -80px;
  opacity: 0.7;
}

.bg-orb--3 {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(201, 171, 99, 0.18) 0%, transparent 70%);
  top: 50%;
  left: 30%;
  transform: translate(-50%, -50%);
  opacity: 0.5;
}

.card {
  background: rgba(255, 252, 244, 0.88);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(201, 171, 99, 0.22);
  border-radius: var(--radius-lg);
  padding: 48px 44px;
  width: 100%;
  max-width: 420px;
  box-shadow:
    0 4px 6px rgba(0, 0, 0, 0.03),
    0 20px 60px rgba(201, 171, 99, 0.12),
    inset 0 1px 0 rgba(255, 255, 255, 0.8);
  animation: cardIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes cardIn {
  from { opacity: 0; transform: translateY(24px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.card__header {
  text-align: center;
  margin-bottom: 36px;
}

.logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, var(--accent) 0%, #e1c06f 100%);
  border-radius: 16px;
  margin-bottom: 20px;
  font-size: 18px;
  font-weight: 800;
  color: var(--text);
  box-shadow: 0 12px 24px rgba(201, 171, 99, 0.28);
}

.card__title {
  font-size: 28px;
  font-weight: 700;
  color: var(--text);
  letter-spacing: -0.3px;
  line-height: 1.2;
  margin-bottom: 6px;
}

.card__subtitle {
  font-size: 13.5px;
  color: var(--text-secondary);
  font-weight: 500;
  letter-spacing: 0.1px;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.field__label {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-secondary);
  letter-spacing: 0.5px;
  text-transform: uppercase;
  transition: color 0.2s;
}

.field--focused .field__label {
  color: var(--accent-strong);
}

.field__wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.field__icon {
  position: absolute;
  left: 14px;
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  pointer-events: none;
  transition: color 0.2s;
}

.field--focused .field__icon {
  color: var(--accent-strong);
}

.field__wrapper input {
  width: 100%;
  padding: 13px 14px 13px 42px;
  background: rgba(255, 252, 245, 0.95);
  border: 1.5px solid var(--surface-border);
  border-radius: var(--radius-sm);
  font-size: 14px;
  color: var(--text);
  outline: none;
  transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}

.field__wrapper input::placeholder {
  color: var(--text-secondary);
  opacity: 0.6;
}

.field__wrapper input:focus {
  border-color: color-mix(in srgb, var(--accent) 75%, white 25%);
  background: #ffffff;
  box-shadow: 0 0 0 4px rgba(201, 171, 99, 0.16);
}

.field__toggle {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  padding: 4px;
  border-radius: 6px;
  transition: color 0.2s, background 0.2s;
}

.field__toggle:hover {
  color: var(--text);
  background: rgba(0, 0, 0, 0.04);
}

.error-banner {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 11px 14px;
  background: var(--danger-bg);
  border: 1px solid rgba(220, 38, 38, 0.18);
  border-radius: var(--radius-sm);
  font-size: 13px;
  color: #8f3b3b;
}

.error-enter-active,
.error-leave-active {
  transition: all 0.25s ease;
}

.error-enter-from,
.error-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.submit-btn {
  margin-top: 6px;
  padding: 14px;
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  color: var(--text);
  border: none;
  border-radius: var(--radius-sm);
  font-size: 15px;
  font-weight: 800;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.2s, filter 0.2s;
  box-shadow: 0 12px 26px rgba(201, 171, 99, 0.28);
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
}

.submit-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 32px rgba(201, 171, 99, 0.36);
  filter: brightness(1.04);
}

.submit-btn:active {
  transform: translateY(0);
  box-shadow: 0 6px 14px rgba(201, 171, 99, 0.22);
}

.submit-btn--loading {
  pointer-events: none;
  opacity: 0.75;
}

.loader {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(26, 26, 26, 0.25);
  border-top-color: var(--text);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.card__footer {
  text-align: center;
  margin-top: 28px;
  font-size: 12px;
  color: var(--text-secondary);
  letter-spacing: 0.2px;
}
</style>

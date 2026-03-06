<template>
  <div class="login-page">
    <!-- Animated background -->
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>

    <div class="login-card">
      <!-- Left decorative panel -->
      <div class="card-art">
        <div class="art-inner">
          <div class="art-logo">
            <svg width="42" height="42" viewBox="0 0 42 42" fill="none">
              <rect width="42" height="42" rx="12" fill="white" fill-opacity="0.15"/>
              <path d="M12 21L21 12L30 21L21 30L12 21Z" stroke="white" stroke-width="2" fill="none"/>
              <circle cx="21" cy="21" r="4" fill="white"/>
            </svg>
          </div>
          <div class="art-text">
            <h2>С возвращением</h2>
            <p>Войдите, чтобы продолжить работу с платформой</p>
          </div>
          <div class="art-features">
            <div class="feat" v-for="f in features" :key="f">
              <div class="feat-dot"></div>
              <span>{{ f }}</span>
            </div>
          </div>
          <div class="art-footer">Версия 2.0 · Безопасно</div>
        </div>
        <div class="art-grid"></div>
      </div>

      <!-- Right form panel -->
      <div class="card-form">
        <div class="form-header">
          <h1>Вход</h1>
          <p>Введите ваши данные ниже</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form" novalidate>
          <div class="field-group">
            <div class="field" :class="{ focused: focusedField === 'email', filled: email }">
              <label>Email</label>
              <div class="input-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="none">
                  <path d="M3 4h14a1 1 0 011 1v10a1 1 0 01-1 1H3a1 1 0 01-1-1V5a1 1 0 011-1z" stroke="currentColor" stroke-width="1.5"/>
                  <path d="M2 6l8 5 8-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <input
                  type="email"
                  v-model="email"
                  placeholder="you@example.com"
                  @focus="focusedField = 'email'"
                  @blur="focusedField = ''"
                  required
                />
              </div>
            </div>

            <div class="field" :class="{ focused: focusedField === 'password', filled: password }">
              <label>Пароль</label>
              <div class="input-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="none">
                  <rect x="3" y="8" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                  <path d="M7 8V6a3 3 0 116 0v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  <circle cx="10" cy="13" r="1.5" fill="currentColor"/>
                </svg>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  v-model="password"
                  placeholder="••••••••"
                  @focus="focusedField = 'password'"
                  @blur="focusedField = ''"
                  required
                />
                <button type="button" class="eye-btn" @click="showPassword = !showPassword" tabindex="-1">
                  <svg v-if="!showPassword" viewBox="0 0 20 20" fill="none">
                    <path d="M1 10s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6z" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                  </svg>
                  <svg v-else viewBox="0 0 20 20" fill="none">
                    <path d="M3 3l14 14M8.5 8.5A2.5 2.5 0 0112.5 12M6 5.5C3.5 7 2 10 2 10s3.5 5 8 5c1.5 0 3-.4 4.2-1M14 13.5C15.8 12 18 10 18 10s-3.5-6-8-6c-.8 0-1.5.1-2.2.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <transition name="err">
            <div v-if="error" class="error-banner">
              <svg viewBox="0 0 20 20" fill="none">
                <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
                <path d="M10 6v5M10 14h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              {{ error }}
            </div>
          </transition>

          <button type="submit" class="submit-btn" :class="{ loading }" :disabled="loading">
            <span class="btn-text">{{ loading ? 'Подождите...' : 'Войти в аккаунт' }}</span>
            <div class="btn-spinner" v-if="loading"></div>
            <svg v-else class="btn-arrow" viewBox="0 0 20 20" fill="none">
              <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </form>

        <div class="form-footer">
          <p>Нет аккаунта? <router-link to="/register">Зарегистрироваться</router-link></p>
        </div>
      </div>
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
const focusedField = ref('')

const features = ['Безопасный вход', 'Защита данных', 'Быстрый доступ']

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
  const response = await api.post('/auth/login', {
  email: email.value,
  password: password.value
})

userStore.setAuth(
  response.data.user,
  response.data.token
)

router.push('/')


  } catch (err) {
    if (err.response?.status === 401) {
      error.value = 'Неверный email или пароль'
    } else if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)[0][0]
    } else {
      error.value = 'Ошибка сервера. Попробуйте позже.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.login-page {
  font-family: 'Sora', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f4f9;
  padding: 24px;
  position: relative;
  overflow: hidden;
}

/* --- Animated orbs --- */
.bg-orbs {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.45;
  animation: drift 12s ease-in-out infinite alternate;
}

.orb-1 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, #c7d2fe, transparent 70%);
  top: -120px;
  left: -100px;
  animation-duration: 14s;
}

.orb-2 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, #bfdbfe, transparent 70%);
  bottom: -80px;
  right: -60px;
  animation-duration: 10s;
  animation-delay: -4s;
}

.orb-3 {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, #ddd6fe, transparent 70%);
  top: 40%;
  left: 50%;
  opacity: 0.35;
  animation-duration: 16s;
  animation-delay: -7s;
}

@keyframes drift {
  from { transform: translate(0, 0) scale(1); }
  to { transform: translate(30px, 40px) scale(1.1); }
}

/* --- Card --- */
.login-card {
  display: flex;
  width: 100%;
  max-width: 900px;
  min-height: 560px;
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(99, 102, 241, 0.1), 0 0 0 1px rgba(0,0,0,0.06);
  position: relative;
  z-index: 1;
  animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes cardIn {
  from { opacity: 0; transform: translateY(30px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/* --- Art panel --- */
.card-art {
  width: 42%;
  background: linear-gradient(145deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
  padding: 48px 40px;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.art-grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
  background-size: 40px 40px;
  mask-image: linear-gradient(to bottom right, transparent 20%, black 60%);
}

.art-inner {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  gap: 28px;
}

.art-logo {
  display: inline-flex;
}

.art-text h2 {
  font-size: 26px;
  font-weight: 700;
  color: white;
  margin-bottom: 10px;
  line-height: 1.2;
}

.art-text p {
  font-size: 14px;
  color: rgba(255,255,255,0.65);
  line-height: 1.6;
}

.art-features {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: auto;
}

.feat {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  color: rgba(255,255,255,0.75);
}

.feat-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: rgba(255,255,255,0.6);
  flex-shrink: 0;
}

.art-footer {
  font-size: 11px;
  color: rgba(255,255,255,0.35);
  font-family: 'JetBrains Mono', monospace;
}

/* --- Form panel --- */
.card-form {
  flex: 1;
  background: #ffffff;
  padding: 52px 48px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.form-header {
  margin-bottom: 36px;
}

.form-header h1 {
  font-size: 32px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 8px;
  letter-spacing: -0.5px;
}

.form-header p {
  font-size: 14px;
  color: #9ca3af;
}

/* --- Fields --- */
.field-group {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 20px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

label {
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  transition: color 0.2s;
}

.field.focused label {
  color: #6366f1;
}

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.field-icon {
  position: absolute;
  left: 14px;
  width: 18px;
  height: 18px;
  color: #d1d5db;
  transition: color 0.2s;
  pointer-events: none;
}

.field.focused .field-icon {
  color: #6366f1;
}

input {
  width: 100%;
  background: #f9fafb;
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  padding: 13px 44px 13px 42px;
  font-size: 15px;
  font-family: 'Sora', sans-serif;
  color: #111827;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  outline: none;
}

input::placeholder {
  color: #c4c9d4;
}

input:focus {
  border-color: #6366f1;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.eye-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #d1d5db;
  display: flex;
  align-items: center;
  transition: color 0.2s;
}

.eye-btn:hover { color: #6b7280; }

.eye-btn svg {
  width: 18px;
  height: 18px;
}

/* --- Error banner --- */
.error-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 13px;
  color: #fca5a5;
  margin-bottom: 4px;
}

.error-banner svg {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.err-enter-active, .err-leave-active { transition: all 0.3s ease; }
.err-enter-from, .err-leave-to { opacity: 0; transform: translateY(-6px); }

/* --- Submit button --- */
.submit-btn {
  width: 100%;
  padding: 15px 24px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  font-size: 15px;
  font-weight: 600;
  font-family: 'Sora', sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.35);
  margin-top: 24px;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 28px rgba(99, 102, 241, 0.45);
}

.submit-btn:active:not(:disabled) {
  transform: translateY(0);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-arrow {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.btn-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* --- Footer --- */
.form-footer {
  margin-top: 24px;
  text-align: center;
}

.form-footer p {
  font-size: 14px;
  color: #9ca3af;
}

.form-footer a {
  color: #6366f1;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.form-footer a:hover {
  color: #4f46e5;
}

/* --- Responsive --- */
@media (max-width: 768px) {
  .card-art {
    display: none;
  }

  .card-form {
    padding: 40px 32px;
  }

  .form-header h1 {
    font-size: 26px;
  }
}

@media (max-width: 480px) {
  .card-form {
    padding: 32px 24px;
  }
}
</style>